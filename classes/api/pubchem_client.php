<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_chemillusion\api;

use local_chemillusion\cache\molecule_cache;
use local_chemillusion\util\input_normalizer;

/**
 * Minimal PubChem PUG REST client.
 *
 * Resolves an identifier to a compact public metadata payload and caches the
 * result server-side. Honours the admin "disable external" and "enable
 * PubChem" switches. Never stores per-student query history.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pubchem_client {

    /** @var string PubChem PUG REST base. */
    const BASE = 'https://pubchem.ncbi.nlm.nih.gov/rest/pug';

    /** @var string PubChem compound view base for human links. */
    const VIEW = 'https://pubchem.ncbi.nlm.nih.gov/compound/';

    /** @var string The requested properties (PubChem property tags). */
    const PROPS = 'MolecularFormula,MolecularWeight,CanonicalSMILES,IsomericSMILES,InChIKey,IUPACName';

    /**
     * Resolve an identifier into a normalised payload.
     *
     * If input is detected as SMILES, only SMILES lookup is attempted (no fallback to text search).
     * Otherwise, name search is used. Caller can override with force_type to retry.
     * If PubChem fails, returns a fallback response with cached/parsed data if available.
     *
     * @param string $rawinput User-supplied identifier.
     * @param string|null $force_type Override detected type (e.g., to retry as 'name' after SMILES fails).
     * @return array{status:string, data?:array, error?:string, fallback?:bool, alt_types?:array}
     */
    public static function resolve($rawinput, $force_type = null) {
        $value = input_normalizer::normalize($rawinput);
        if (!input_normalizer::is_nonempty($value)) {
            return ['status' => 'error', 'error' => 'invalidinput'];
        }

        if (get_config('local_chemillusion', 'disable_external')
                || !get_config('local_chemillusion', 'enable_pubchem')) {
            return ['status' => 'error', 'error' => 'external_disabled'];
        }

        $detected_type = input_normalizer::detect_type($value);
        $type = $force_type ?: $detected_type;
        $cachekey = molecule_cache::make_key($type, $value);

        $cached = molecule_cache::get($cachekey);
        if (is_array($cached)) {
            return ['status' => 'ok', 'data' => $cached, 'cached' => true];
        }

        // Try the lookup; if it fails and we detected SMILES, offer name search as alternative.
        $result = self::fetch_properties($type, $value);
        if ($result['status'] === 'ok') {
            $ttl = (int) get_config('local_chemillusion', 'cache_ttl');
            molecule_cache::set($cachekey, $result['data'], $ttl);
        } else if ($result['status'] === 'error' && $type === input_normalizer::TYPE_SMILES && !$force_type) {
            // SMILES lookup failed and user didn't force an override.
            // Suggest text search as alternative.
            $result['alt_types'] = [input_normalizer::TYPE_NAME];
            $result['error_note'] = 'SMILES lookup failed. You can try searching by name instead.';
        } else if (in_array($result['error'], ['ratelimited', 'network'])) {
            // PubChem is down/slow. Return fallback with parsed data from input.
            $fallback = self::make_fallback_from_input($value, $type);
            $fallback['fallback'] = true;
            $fallback['error_note'] = 'PubChem is temporarily unavailable. Showing parsed data. Try again later for full metadata.';
            return $fallback;
        }
        return $result;
    }

    /**
     * Perform the HTTP property lookup against PubChem.
     *
     * @param string $type One of the input_normalizer TYPE_* values.
     * @param string $value
     * @return array
     */
    protected static function fetch_properties($type, $value) {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $namespace = self::namespace_for_type($type);
        $propurl = self::BASE . "/compound/{$namespace}/property/" . self::PROPS . '/JSON';

        $curl = new \curl();
        $options = ['CURLOPT_TIMEOUT' => 12, 'CURLOPT_CONNECTTIMEOUT' => 6];

        if ($type === input_normalizer::TYPE_INCHI) {
            // InChI must be POSTed because it contains reserved URL characters.
            $response = $curl->post($propurl, ['inchi' => $value], $options);
        } else {
            $url = self::BASE . "/compound/{$namespace}/" . rawurlencode($value)
                . '/property/' . self::PROPS . '/JSON';
            $response = $curl->get($url, [], $options);
        }

        $info = $curl->get_info();
        $httpcode = isset($info['http_code']) ? (int) $info['http_code'] : 0;

        if ($httpcode === 404) {
            return ['status' => 'error', 'error' => 'nomatch'];
        }
        if ($httpcode === 429 || $httpcode === 503) {
            return ['status' => 'error', 'error' => 'ratelimited'];
        }
        if ($httpcode !== 200 || $response === false || $response === '') {
            return ['status' => 'error', 'error' => 'network'];
        }

        $decoded = json_decode($response, true);
        if (!isset($decoded['PropertyTable']['Properties'][0])) {
            return ['status' => 'error', 'error' => 'nomatch'];
        }

        return ['status' => 'ok', 'data' => self::map_payload($decoded['PropertyTable']['Properties'][0])];
    }

    /**
     * Map a PubChem property record to the plugin payload shape.
     *
     * @param array $p
     * @return array
     */
    protected static function map_payload(array $p) {
        $cid = isset($p['CID']) ? (int) $p['CID'] : 0;
        return [
            'name'             => isset($p['IUPACName']) ? (string) $p['IUPACName'] : '',
            'cid'              => $cid,
            'formula'          => isset($p['MolecularFormula']) ? (string) $p['MolecularFormula'] : '',
            'mw'               => isset($p['MolecularWeight']) ? (string) $p['MolecularWeight'] : '',
            'canonical_smiles' => isset($p['CanonicalSMILES']) ? (string) $p['CanonicalSMILES'] : '',
            'isomeric_smiles'  => isset($p['IsomericSMILES']) ? (string) $p['IsomericSMILES'] : '',
            'inchikey'         => isset($p['InChIKey']) ? (string) $p['InChIKey'] : '',
            'pubchem_url'      => $cid ? self::VIEW . $cid : '',
        ];
    }

    /**
     * Map an input type to the PubChem REST namespace segment.
     *
     * @param string $type
     * @return string
     */
    protected static function namespace_for_type($type) {
        switch ($type) {
            case input_normalizer::TYPE_SMILES:
                return 'smiles';
            case input_normalizer::TYPE_INCHI:
                return 'inchi';
            case input_normalizer::TYPE_NAME:
            default:
                return 'name';
        }
    }

    /**
     * Create a minimal fallback response when PubChem is down.
     *
     * Uses input value and type to construct what data we can without the remote service.
     * User is shown a note to try again later for complete data.
     *
     * @param string $value
     * @param string $type
     * @return array{status:string, data:array}
     */
    protected static function make_fallback_from_input($value, $type) {
        $data = [
            'name'             => $value,
            'cid'              => 0,
            'formula'          => '',
            'mw'               => '',
            'canonical_smiles' => $type === input_normalizer::TYPE_SMILES ? $value : '',
            'isomeric_smiles'  => '',
            'inchikey'         => '',
            'pubchem_url'      => '',
        ];

        // If the input is SMILES, we can at least show what was entered.
        if ($type === input_normalizer::TYPE_SMILES) {
            $data['name'] = '(SMILES: ' . $value . ')';
            $data['canonical_smiles'] = $value;
        }

        return ['status' => 'ok', 'data' => $data];
    }
}
