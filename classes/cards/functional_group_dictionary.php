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
 * Loads and searches the bundled functional-group registry. The SMARTS values
 * here are also handed to the browser for RDKit.js matching in Phase 1B.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class functional_group_dictionary {
    /** @var array|null In-memory cache. */
    protected static $cache = null;

    /**
     * Return the full registry keyed by group id.
     *
     * @return array
     */
    public static function all() {
        global $CFG;
        if (self::$cache !== null) {
            return self::$cache;
        }
        $path = $CFG->dirroot . '/local/chemillusion/resources/functional_groups.json';
        $data = is_readable($path) ? json_decode(file_get_contents($path), true) : [];
        self::$cache = is_array($data) ? $data : [];
        return self::$cache;
    }

    /**
     * Get a single functional group by id.
     *
     * @param string $id
     * @return array|null
     */
    public static function get($id) {
        $all = self::all();
        return isset($all[$id]) ? $all[$id] : null;
    }

    /**
     * Compact id => SMARTS map for the browser matcher.
     *
     * @return array
     */
    public static function smarts_registry() {
        $registry = [];
        foreach (self::all() as $id => $data) {
            if (!empty($data['smarts'])) {
                $registry[] = [
                    'id'     => $id,
                    'label'  => isset($data['label']) ? $data['label'] : $id,
                    'smarts' => $data['smarts'],
                ];
            }
        }
        return $registry;
    }

    /**
     * Search functional groups by id or label fragment.
     *
     * @param string $query
     * @return array
     */
    public static function search($query) {
        $query = \core_text::strtolower(trim($query));
        if ($query === '') {
            return self::all();
        }
        $results = [];
        foreach (self::all() as $id => $data) {
            $label = isset($data['label']) ? $data['label'] : '';
            if (
                strpos(\core_text::strtolower($id), $query) !== false
                || strpos(\core_text::strtolower($label), $query) !== false
            ) {
                $results[$id] = $data;
            }
        }
        return $results;
    }
}
