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

namespace local_chemillusion\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Generates deterministic accessible text summaries from graphical card JSON.
 *
 * All output is template-driven and conservative — no hallucinated chemistry claims.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class card_accessibility_summary {
    /**
     * Generate a plain-text accessible summary string for a card.
     *
     * @param string $type     Card type id.
     * @param array  $front    Decoded frontjson.
     * @param array  $back     Decoded backjson (may be empty).
     * @return string
     */
    public static function generate(string $type, array $front, array $back = []): string {
        switch ($type) {
            case 'newman_projection':
                return self::newman($front);
            case 'reaction_coordinate':
                return self::reaction_coordinate($front);
            case 'orbital_hybridization':
                return self::orbital($front);
            case 'molecule_identity':
            case 'functional_group_highlight':
            case 'functional_group_list':
            case 'smiles_to_structure':
            case 'structure_to_smiles':
            case 'accessibility_card':
                return self::molecule($front, $back);
            case 'reagent_card':
                return self::reagent($front, $back);
            default:
                return get_string('accessible_alt_default', 'local_chemillusion');
        }
    }

    /**
     * Newman projection summary.
     *
     * @param array $front
     * @return string
     */
    private static function newman(array $front): string {
        $bond  = $front['bond_label'] ?? 'C–C';
        $flist = implode(', ', $front['front'] ?? ['?', '?', '?']);
        $blist = implode(', ', $front['back'] ?? ['?', '?', '?']);
        $rot   = $front['rotation_degrees'] ?? 0;
        $conf  = $front['conformation'] ?? '';
        $conf  = $conf ? " Conformation: {$conf}." : '';
        return get_string('newman_accessibility_summary', 'local_chemillusion', [
            'bond'     => $bond,
            'front'    => $flist,
            'back'     => $blist,
            'rotation' => $rot,
        ]) . $conf;
    }

    /**
     * Reaction coordinate summary.
     *
     * @param array $front
     * @return string
     */
    private static function reaction_coordinate(array $front): string {
        $title  = $front['title'] ?? 'Reaction';
        $points = $front['points'] ?? [];
        $ts     = array_filter($points, fn($p) => str_starts_with(strtolower($p['id'] ?? ''), 'ts'));
        $int    = array_filter($points, fn($p) => str_starts_with(strtolower($p['id'] ?? ''), 'int'));
        // Determine direction from first and last point y values (lower y = higher energy in SVG).
        $first = reset($points);
        $last  = end($points);
        $direction = '';
        if ($first && $last) {
            $direction = ($last['y'] > $first['y'])
                ? 'The overall reaction is exergonic (products lower in energy).'
                : 'The overall reaction is endergonic (products higher in energy).';
        }
        return get_string('rcd_accessibility_summary', 'local_chemillusion', [
            'title'     => $title,
            'ts_count'  => count($ts),
            'int_count' => count($int),
            'direction' => $direction,
        ]);
    }

    /**
     * Orbital diagram summary.
     *
     * @param array $front
     * @return string
     */
    private static function orbital(array $front): string {
        $label = $front['label'] ?? ($front['smiles'] ?? 'molecule');
        $hyb   = $front['hybridization'] ?? '';
        $topic = $front['topic'] ?? '';
        $parts = ["Orbital diagram for {$label}."];
        if ($hyb) {
            $parts[] = "Hybridization: {$hyb}.";
        }
        if ($topic) {
            $parts[] = ucfirst($topic) . '.';
        }
        return implode(' ', $parts);
    }

    /**
     * Molecule card summary.
     *
     * @param array $front
     * @param array $back
     * @return string
     */
    private static function molecule(array $front, array $back): string {
        $name    = $front['name'] ?? ($back['name'] ?? '');
        $smiles  = $front['smiles'] ?? ($back['canonical_smiles'] ?? '');
        $formula = $back['formula'] ?? '';
        $groups  = $back['functional_groups'] ?? [];
        $parts   = [];
        if ($name) {
            $parts[] = "Molecule: {$name}.";
        }
        if ($formula) {
            $parts[] = "Formula: {$formula}.";
        }
        if ($smiles) {
            $parts[] = "SMILES: {$smiles}.";
        }
        if ($groups) {
            $parts[] = 'Functional groups: ' . implode(', ', $groups) . '.';
        }
        return $parts ? implode(' ', $parts) : get_string('accessible_alt_default', 'local_chemillusion');
    }

    /**
     * Reagent card summary.
     *
     * @param array $front
     * @param array $back
     * @return string
     */
    private static function reagent(array $front, array $back): string {
        $acronym = $front['acronym'] ?? '';
        $name    = $back['full_name'] ?? '';
        $role    = $back['role'] ?? '';
        $parts   = [];
        if ($acronym) {
            $parts[] = "Reagent: {$acronym}.";
        }
        if ($name) {
            $parts[] = "Full name: {$name}.";
        }
        if ($role) {
            $parts[] = "Role: {$role}.";
        }
        return $parts ? implode(' ', $parts) : get_string('accessible_alt_default', 'local_chemillusion');
    }
}
