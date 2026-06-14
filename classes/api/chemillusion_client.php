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

use local_chemillusion\security\signed_state;

/**
 * Builds links into ChemIllusion and (optionally) sends minimal conversion
 * metadata. This is a thin public-contract client only: no private prompts,
 * billing, model routing, or Ketcher overlay internals live here.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class chemillusion_client {

    /**
     * Configured ChemIllusion base URL, without trailing slash.
     *
     * @return string
     */
    public static function base_url() {
        $url = (string) get_config('local_chemillusion', 'chemillusion_base_url');
        if ($url === '') {
            $url = 'https://chemillusion.com';
        }
        return rtrim($url, '/');
    }

    /**
     * Whether account linking is enabled by the administrator.
     *
     * @return bool
     */
    public static function linking_enabled() {
        return (bool) get_config('local_chemillusion', 'enable_account_linking');
    }

    /**
     * Build minimal, PII-free source metadata for a CTA.
     *
     * @param string $role student|teacher|admin
     * @param string $surface study_card|molecule_lookup|teacher_dashboard
     * @param string $cta CTA identifier
     * @return array
     */
    public static function build_source_metadata($role, $surface, $cta) {
        global $CFG;
        return [
            'source'           => 'moodle',
            'plugin_component' => 'local_chemillusion',
            'plugin_version'   => get_config('local_chemillusion', 'version'),
            'site_hash'        => sha1(get_site_identifier()),
            'role'             => clean_param($role, PARAM_ALPHA),
            'surface'          => clean_param($surface, PARAM_ALPHANUMEXT),
            'cta'              => clean_param($cta, PARAM_ALPHANUMEXT),
        ];
    }

    /**
     * Build a signed account-link start URL into ChemIllusion.
     *
     * @param array $meta Source metadata from build_source_metadata().
     * @return string
     */
    public static function link_start_url(array $meta) {
        $state = signed_state::sign($meta);
        return self::base_url() . '/moodle/link?state=' . rawurlencode($state);
    }

    /**
     * Build a signed "continue / open in ChemIllusion" URL with molecule context.
     *
     * @param array $meta Source metadata.
     * @param array $context Optional molecule context (smiles, name, cid).
     * @return string
     */
    public static function continue_url(array $meta, array $context = []) {
        $meta['context'] = self::safe_context($context);
        $state = signed_state::sign($meta);
        return self::base_url() . '/moodle/continue?state=' . rawurlencode($state);
    }

    /**
     * Build a signed visual-card start URL with molecule context.
     *
     * @param array $meta Source metadata.
     * @param array $context Molecule context.
     * @return string
     */
    public static function visual_card_url(array $meta, array $context = []) {
        $meta['context'] = self::safe_context($context);
        $state = signed_state::sign($meta);
        return self::base_url() . '/moodle/visual-card?state=' . rawurlencode($state);
    }

    /**
     * Build the teacher demo URL.
     *
     * @return string
     */
    public static function teacher_demo_url() {
        return self::base_url() . '/moodle/teacher-demo';
    }

    /**
     * Reduce molecule context to safe, public fields only.
     *
     * @param array $context
     * @return array
     */
    protected static function safe_context(array $context) {
        $allowed = ['name', 'cid', 'formula', 'canonical_smiles', 'isomeric_smiles', 'inchikey', 'group'];
        $clean = [];
        foreach ($allowed as $key) {
            if (isset($context[$key]) && $context[$key] !== '') {
                $clean[$key] = (string) $context[$key];
            }
        }
        return $clean;
    }

    /**
     * Best-effort POST of a conversion event to the ChemIllusion public API.
     * Silently no-ops unless explicitly enabled and external calls are allowed.
     *
     * @param string $eventname
     * @param array $meta
     * @return bool True if a request was attempted and accepted.
     */
    public static function send_conversion_metadata($eventname, array $meta) {
        global $CFG;
        if (get_config('local_chemillusion', 'disable_external')
                || !get_config('local_chemillusion', 'enable_conversion_metadata')) {
            return false;
        }
        require_once($CFG->libdir . '/filelib.php');

        $url = self::base_url() . '/api/public/moodle/conversion';
        $meta['event'] = clean_param($eventname, PARAM_ALPHANUMEXT);

        $curl = new \curl();
        $curl->setHeader('Content-Type: application/json');
        $response = $curl->post($url, json_encode($meta),
            ['CURLOPT_TIMEOUT' => 6, 'CURLOPT_CONNECTTIMEOUT' => 4]);
        $info = $curl->get_info();
        return isset($info['http_code']) && (int) $info['http_code'] >= 200 && (int) $info['http_code'] < 300;
    }
}
