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
 * Registry of reaction coordinate diagram templates from data/reaction_coordinate_templates.json.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reaction_coordinate_template_registry {
    /** @var array|null */
    private static $templates = null;

    /**
     * Return all templates.
     *
     * @return array
     */
    public static function get_all(): array {
        return self::load();
    }

    /**
     * Return a single template by id, or null.
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
     * Return default frontjson card data for a template id.
     *
     * @param string $id
     * @return array|null
     */
    public static function default_card_data(string $id): ?array {
        $t = self::get($id);
        if ($t === null) {
            return null;
        }
        return [
            'type'        => 'reaction_coordinate',
            'title'       => $t['label'],
            'template'    => $t['id'],
            'x_axis'      => $t['x_axis_label'] ?? 'Reaction coordinate',
            'y_axis'      => $t['y_axis_label'] ?? 'Free energy',
            'points'      => $t['points'],
            'annotations' => $t['annotations'] ?? [],
            'disclaimer'  => true,
            'teacher_note' => '',
        ];
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
        $path = __DIR__ . '/../../data/reaction_coordinate_templates.json';
        $json = file_get_contents($path);
        self::$templates = json_decode($json, true) ?? [];
        return self::$templates;
    }
}
