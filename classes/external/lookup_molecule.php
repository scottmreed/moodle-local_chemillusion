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

namespace local_chemillusion\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use local_chemillusion\api\pubchem_client;
use local_chemillusion\util\input_normalizer;
use local_chemillusion\telemetry\local_event_logger;

defined('MOODLE_INTERNAL') || die();

/**
 * AJAX endpoint: resolve a molecule identifier via PubChem (cached).
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lookup_molecule extends external_api {

    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'query' => new external_value(PARAM_TEXT, 'Name, SMILES, InChI, or InChIKey'),
        ]);
    }

    /**
     * Resolve the molecule.
     *
     * @param string $query
     * @return array
     */
    public static function execute($query) {
        $params = self::validate_parameters(self::execute_parameters(), ['query' => $query]);

        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/chemillusion:view', $context);

        $type = input_normalizer::detect_type($params['query']);
        $result = pubchem_client::resolve($params['query']);

        if ($result['status'] === 'ok') {
            local_event_logger::log(local_event_logger::EVENT_LOOKUP, 'molecule_lookup');
            $data = $result['data'];
            return [
                'status'           => 'ok',
                'inputtype'        => $type,
                'name'             => $data['name'],
                'cid'              => (int) $data['cid'],
                'formula'          => $data['formula'],
                'mw'               => $data['mw'],
                'canonical_smiles' => $data['canonical_smiles'],
                'isomeric_smiles'  => $data['isomeric_smiles'],
                'inchikey'         => $data['inchikey'],
                'pubchem_url'      => $data['pubchem_url'],
            ];
        }

        return [
            'status'    => 'error',
            'inputtype' => $type,
            'error'     => $result['error'],
        ];
    }

    /**
     * Return description.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'status'           => new external_value(PARAM_ALPHA, 'ok or error'),
            'inputtype'        => new external_value(PARAM_ALPHA, 'Detected input type'),
            'error'            => new external_value(PARAM_ALPHANUMEXT, 'Error code', VALUE_OPTIONAL),
            'name'             => new external_value(PARAM_TEXT, 'Preferred name', VALUE_OPTIONAL),
            'cid'              => new external_value(PARAM_INT, 'PubChem CID', VALUE_OPTIONAL),
            'formula'          => new external_value(PARAM_TEXT, 'Molecular formula', VALUE_OPTIONAL),
            'mw'               => new external_value(PARAM_TEXT, 'Molecular weight', VALUE_OPTIONAL),
            'canonical_smiles' => new external_value(PARAM_TEXT, 'Canonical SMILES', VALUE_OPTIONAL),
            'isomeric_smiles'  => new external_value(PARAM_TEXT, 'Isomeric SMILES', VALUE_OPTIONAL),
            'inchikey'         => new external_value(PARAM_TEXT, 'InChIKey', VALUE_OPTIONAL),
            'pubchem_url'      => new external_value(PARAM_URL, 'PubChem link', VALUE_OPTIONAL),
        ]);
    }
}
