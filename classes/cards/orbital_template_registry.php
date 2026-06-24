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

defined('MOODLE_INTERNAL') || die();

/**
 * Registry of curated orbital/hybridization templates from data/orbital_templates.json.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class orbital_template_registry {
    /** @var array|null */
    private static $templates = null;

    /**
     * Return all curated orbital template entries.
     *
     * @return array
     */
    public static function get_all(): array {
        return self::load();
    }

    /**
     * Return a single entry by id, or null.
     *
     * @param string $id
     * @return array|null
     */
    public static function get(string $id): ?array {
        foreach (self::load() as $t) {
            if ($t['id'] === $id) {
                return $t;
            }
        }
        return null;
    }

    /**
     * Find the best-matching curated entry for a SMILES pattern.
     * Returns null if no curated overlay exists for this structure.
     *
     * @param string $smiles
     * @return array|null
     */
    public static function find_by_smiles(string $smiles): ?array {
        $smiles = trim($smiles);
        foreach (self::load() as $t) {
            if (isset($t['smiles_pattern']) && $t['smiles_pattern'] === $smiles) {
                return $t;
            }
        }
        return null;
    }

    /**
     * Return the absolute pix path for a curated asset, or null if asset missing.
     *
     * @param string $id
     * @return string|null
     */
    public static function asset_path(string $id): ?string {
        $t = self::get($id);
        if ($t === null || empty($t['asset'])) {
            return null;
        }
        $full = __DIR__ . '/../../' . $t['asset'];
        return file_exists($full) ? $full : null;
    }

    /**
     * Load templates from JSON.
     *
     * @return array
     */
    private static function load(): array {
        if (self::$templates !== null) {
            return self::$templates;
        }
        $path = __DIR__ . '/../../data/orbital_templates.json';
        $json = file_get_contents($path);
        self::$templates = json_decode($json, true) ?? [];
        return self::$templates;
    }
}
