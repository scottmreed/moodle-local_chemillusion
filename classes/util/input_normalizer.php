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

namespace local_chemillusion\util;

/**
 * Detects and normalises chemical identifier input.
 *
 * Distinguishes between a molecule name, SMILES, InChI, and InChIKey so the
 * PubChem client can choose the correct REST namespace.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class input_normalizer {
    /** @var string Input looks like a plain name. */
    const TYPE_NAME = 'name';
    /** @var string Input looks like a SMILES string. */
    const TYPE_SMILES = 'smiles';
    /** @var string Input looks like a full InChI. */
    const TYPE_INCHI = 'inchi';

    /**
     * Trim and collapse whitespace.
     *
     * @param string $input
     * @return string
     */
    public static function normalize($input) {
        return trim((string) $input);
    }

    /**
     * Best-effort detection of the identifier type.
     *
     * Prioritizes SMILES detection: if input parses as SMILES, only SMILES lookup is attempted.
     * Falls back to name search only if input is not SMILES.
     *
     * @param string $input
     * @return string One of the TYPE_* constants.
     */
    public static function detect_type($input) {
        $value = self::normalize($input);

        if ($value === '') {
            return self::TYPE_NAME;
        }

        // Full InChI string.
        if (stripos($value, 'InChI=') === 0) {
            return self::TYPE_INCHI;
        }

        // SMILES heuristic: single token containing SMILES-specific characters
        // and no spaces. Names usually contain spaces or are plain words.
        // If this matches, we treat it as SMILES-only (no fallback to text search).
        if (
            strpos($value, ' ') === false
            && preg_match('/[=#\[\]()@+\-\\\\\/]/', $value)
            && preg_match('/[A-Za-z]/', $value)
        ) {
            return self::TYPE_SMILES;
        }

        return self::TYPE_NAME;
    }

    /**
     * Whether the value is safe to use as part of a cache key / URL path.
     *
     * @param string $input
     * @return bool
     */
    public static function is_nonempty($input) {
        return self::normalize($input) !== '';
    }
}
