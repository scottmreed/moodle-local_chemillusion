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
use core_external\external_multiple_structure;
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
            'query'      => new external_value(PARAM_TEXT, 'Name, SMILES, or InChI'),
            'force_type' => new external_value(PARAM_ALPHA, 'Override detected type (name|smiles|inchi)', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Resolve the molecule, with optional type override for retry.
     *
     * @param string $query
     * @param string $force_type Optional type override for retry.
     * @return array
     */
    public static function execute($query, $force_type = '') {
        $params = self::validate_parameters(self::execute_parameters(),
            ['query' => $query, 'force_type' => $force_type]);

        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/chemillusion:view', $context);

        $detected_type = input_normalizer::detect_type($params['query']);
        $result = pubchem_client::resolve($params['query'], $params['force_type'] ?: null);

        if ($result['status'] === 'ok') {
            local_event_logger::log(local_event_logger::EVENT_LOOKUP, 'molecule_lookup');
            $data = $result['data'];
            $response = [
                'status'           => 'ok',
                'inputtype'        => $params['force_type'] ?: $detected_type,
                'name'             => $data['name'],
                'cid'              => (int) $data['cid'],
                'formula'          => $data['formula'],
                'mw'               => $data['mw'],
                'canonical_smiles' => $data['canonical_smiles'],
                'isomeric_smiles'  => $data['isomeric_smiles'],
                'inchikey'         => $data['inchikey'],
                'pubchem_url'      => $data['pubchem_url'],
            ];

            if (!empty($result['fallback'])) {
                $response['fallback'] = true;
                $response['error_note'] = $result['error_note'] ?? '';
            }

            return $response;
        }

        $response = [
            'status'    => 'error',
            'inputtype' => $params['force_type'] ?: $detected_type,
            'error'     => $result['error'],
        ];

        if (!empty($result['error_note'])) {
            $response['error_note'] = $result['error_note'];
        }
        if (!empty($result['alt_types'])) {
            $response['alt_types'] = $result['alt_types'];
        }

        return $response;
    }

    /**
     * Return description.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'status'           => new external_value(PARAM_ALPHA, 'ok or error'),
            'inputtype'        => new external_value(PARAM_ALPHA, 'Detected or forced input type'),
            'error'            => new external_value(PARAM_ALPHANUMEXT, 'Error code', VALUE_OPTIONAL),
            'error_note'       => new external_value(PARAM_TEXT, 'Human-readable error note (e.g., fallback message)', VALUE_OPTIONAL),
            'alt_types'        => new external_multiple_structure(
                new external_value(PARAM_ALPHA, 'Alternative input type'),
                'Alternative types to suggest retrying',
                VALUE_OPTIONAL
            ),
            'fallback'         => new external_value(PARAM_BOOL, 'True if PubChem was down and we returned parsed data', VALUE_OPTIONAL),
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
