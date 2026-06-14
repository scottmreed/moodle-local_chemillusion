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
 * Loads and searches the bundled reagent acronym dictionaries.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reagent_dictionary {

    /** @var array|null In-memory cache of the merged dictionary. */
    protected static $cache = null;

    /**
     * Return the merged reagent dictionary keyed by acronym.
     *
     * @return array
     */
    public static function all() {
        global $CFG;
        if (self::$cache !== null) {
            return self::$cache;
        }
        $dir = $CFG->dirroot . '/local/chemillusion/resources/';
        $merged = [];
        foreach (['reagents_organic1.json', 'reagents_organic2.json'] as $file) {
            $path = $dir . $file;
            if (is_readable($path)) {
                $data = json_decode(file_get_contents($path), true);
                if (is_array($data)) {
                    $merged = array_merge($merged, $data);
                }
            }
        }
        self::$cache = $merged;
        return $merged;
    }

    /**
     * Get a single reagent by acronym (case-insensitive).
     *
     * @param string $acronym
     * @return array|null
     */
    public static function get($acronym) {
        $all = self::all();
        if (isset($all[$acronym])) {
            return $all[$acronym];
        }
        foreach ($all as $key => $value) {
            if (strcasecmp($key, $acronym) === 0) {
                return $value;
            }
        }
        return null;
    }

    /**
     * Search reagents by acronym or name fragment.
     *
     * @param string $query
     * @return array Map of acronym => data.
     */
    public static function search($query) {
        $query = \core_text::strtolower(trim($query));
        if ($query === '') {
            return self::all();
        }
        $results = [];
        foreach (self::all() as $acr => $data) {
            $name = isset($data['name']) ? $data['name'] : '';
            if (strpos(\core_text::strtolower($acr), $query) !== false
                    || strpos(\core_text::strtolower($name), $query) !== false) {
                $results[$acr] = $data;
            }
        }
        return $results;
    }
}
