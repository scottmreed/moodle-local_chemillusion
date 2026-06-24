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

namespace local_chemillusion\cards;


/**
 * Validates graphical card JSON payloads before persistence.
 *
 * Each card type has required frontjson keys. Unknown extra keys are allowed.
 * All string values are expected to be sanitised before SVG injection by the renderer.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class graphical_card_schema {
    /** Required frontjson keys per card type. */
    private const REQUIRED = [
        'molecule_identity'          => ['smiles'],
        'functional_group_highlight' => ['smiles', 'group_id'],
        'functional_group_list'      => ['smiles'],
        'smiles_to_structure'        => ['smiles'],
        'structure_to_smiles'        => ['smiles'],
        'reagent_card'               => ['acronym'],
        'accessibility_card'         => ['smiles'],
        'newman_projection'          => ['front', 'back', 'rotation_degrees'],
        'orbital_hybridization'      => ['smiles'],
        'reaction_coordinate'        => ['template', 'points'],
    ];

    /**
     * Validate a frontjson payload for the given card type.
     *
     * @param string $type  Card type id.
     * @param array  $data  Decoded frontjson array.
     * @return bool
     */
    public static function validate(string $type, array $data): bool {
        $required = self::REQUIRED[$type] ?? null;
        if ($required === null) {
            return false;
        }
        foreach ($required as $key) {
            if (!array_key_exists($key, $data)) {
                return false;
            }
        }
        // Type-specific structural checks.
        if ($type === 'newman_projection') {
            if (!is_array($data['front']) || count($data['front']) !== 3) {
                return false;
            }
            if (!is_array($data['back']) || count($data['back']) !== 3) {
                return false;
            }
            if (!is_numeric($data['rotation_degrees'])) {
                return false;
            }
        }
        if ($type === 'reaction_coordinate') {
            if (!is_array($data['points']) || count($data['points']) < 2) {
                return false;
            }
            foreach ($data['points'] as $pt) {
                if (!isset($pt['id'], $pt['x'], $pt['y'], $pt['label'])) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Return a list of validation error descriptions for a payload, or empty array if valid.
     *
     * @param string $type
     * @param array  $data
     * @return string[]
     */
    public static function errors(string $type, array $data): array {
        $errs = [];
        $required = self::REQUIRED[$type] ?? null;
        if ($required === null) {
            return ["Unknown card type: {$type}"];
        }
        foreach ($required as $key) {
            if (!array_key_exists($key, $data)) {
                $errs[] = "Missing required field: {$key}";
            }
        }
        return $errs;
    }
}
