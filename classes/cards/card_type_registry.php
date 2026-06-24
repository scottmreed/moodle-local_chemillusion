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
 * Registry of graphical card types loaded from data/card_type_registry.json.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class card_type_registry {
    /** @var array|null Cached registry entries. */
    private static $registry = null;

    /**
     * Return all registered card types.
     *
     * @return array Array of type definition objects.
     */
    public static function get_all(): array {
        return self::load();
    }

    /**
     * Return a single type definition by id or null if not found.
     *
     * @param string $typeid
     * @return array|null
     */
    public static function get(string $typeid): ?array {
        foreach (self::load() as $type) {
            if ($type['id'] === $typeid) {
                return $type;
            }
        }
        return null;
    }

    /**
     * Return all type ids enabled for the current admin configuration.
     *
     * @return array
     */
    public static function get_enabled_types(): array {
        $cfg = [
            'molecule_identity'         => true,
            'functional_group_highlight' => true,
            'functional_group_list'      => true,
            'smiles_to_structure'        => true,
            'structure_to_smiles'        => true,
            'reagent_card'               => true,
            'accessibility_card'         => true,
            'newman_projection'          => (bool) get_config('local_chemillusion', 'enable_newman_cards'),
            'orbital_hybridization'      => (bool) get_config('local_chemillusion', 'enable_orbital_cards'),
            'reaction_coordinate'        => (bool) get_config('local_chemillusion', 'enable_reaction_coordinate_cards'),
        ];

        return array_keys(array_filter($cfg));
    }

    /**
     * Load and cache the JSON registry.
     *
     * @return array
     */
    private static function load(): array {
        if (self::$registry !== null) {
            return self::$registry;
        }
        $path = __DIR__ . '/../../data/card_type_registry.json';
        $json = file_get_contents($path);
        self::$registry = json_decode($json, true) ?? [];
        return self::$registry;
    }
}
