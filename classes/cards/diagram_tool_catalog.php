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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

namespace local_chemillusion\cards;

defined('MOODLE_INTERNAL') || die();

/**
 * Root-page diagram tools and their focused editor defaults.
 *
 * @package local_chemillusion
 */
final class diagram_tool_catalog {

    /**
     * Return diagram tools in dashboard display order.
     *
     * @param bool $enabledonly Exclude card types disabled by site settings.
     * @return array
     */
    public static function get_all(bool $enabledonly = true): array {
        $tools = [
            [
                'id' => 'molecule',
                'cardtype' => 'smiles_to_structure',
                'titlekey' => 'diagram_tool_molecule',
                'descriptionkey' => 'diagram_tool_molecule_desc',
                'illustration' => 'molecule',
                'imagealtkey' => 'diagram_tool_molecule_alt',
            ],
            [
                'id' => 'newman',
                'cardtype' => 'newman_projection',
                'titlekey' => 'diagram_tool_newman',
                'descriptionkey' => 'diagram_tool_newman_desc',
                'illustration' => 'newman',
                'imagealtkey' => 'diagram_tool_newman_alt',
            ],
            [
                'id' => 'reaction',
                'cardtype' => 'reaction_coordinate',
                'titlekey' => 'diagram_tool_reaction',
                'descriptionkey' => 'diagram_tool_reaction_desc',
                'illustration' => 'reaction',
                'imagealtkey' => 'diagram_tool_reaction_alt',
            ],
            [
                'id' => 'orbital',
                'cardtype' => 'orbital_hybridization',
                'titlekey' => 'diagram_tool_orbital',
                'descriptionkey' => 'diagram_tool_orbital_desc',
                'illustration' => 'orbital',
                'imagealtkey' => 'diagram_tool_orbital_alt',
            ],
        ];

        $tools = array_map(static function(array $tool): array {
            $tool['editorparams'] = ['tool' => $tool['id']];
            return $tool;
        }, $tools);

        if ($enabledonly) {
            $enabledtypes = card_type_registry::get_enabled_types();
            $tools = array_filter($tools, static function(array $tool) use ($enabledtypes): bool {
                return in_array($tool['cardtype'], $enabledtypes, true);
            });
        }

        return array_values($tools);
    }

    /**
     * Return one tool definition, or null for an unknown or disabled tool.
     *
     * @param string $id Tool id.
     * @param bool $enabledonly Exclude disabled card types.
     * @return array|null
     */
    public static function get(string $id, bool $enabledonly = true): ?array {
        foreach (self::get_all($enabledonly) as $tool) {
            if ($tool['id'] === $id) {
                return $tool;
            }
        }
        return null;
    }

    /**
     * Return the focused tool for a stored card type.
     *
     * @param string $cardtype Card type id.
     * @param bool $enabledonly Exclude disabled card types.
     * @return array|null
     */
    public static function get_by_cardtype(string $cardtype, bool $enabledonly = true): ?array {
        foreach (self::get_all($enabledonly) as $tool) {
            if ($tool['cardtype'] === $cardtype) {
                return $tool;
            }
        }
        return null;
    }

    /**
     * Return a complete, valid first diagram for a tool.
     *
     * @param string $id Tool id.
     * @return array|null
     */
    public static function get_editor_defaults(string $id): ?array {
        switch ($id) {
            case 'molecule':
                return [
                    'name' => '1-bromo-4-chlorobutane',
                    'cardtype' => 'smiles_to_structure',
                    'smiles' => 'ClCCCCBr',
                    'molecule_name' => '1-bromo-4-chlorobutane',
                ];
            case 'newman':
                $newman = newman_template_registry::default_card_data('butane_anti');
                return [
                    'name' => $newman['title'],
                    'cardtype' => 'newman_projection',
                    'newman_preset' => 'butane_anti',
                    'newman_front_a' => $newman['front'][0],
                    'newman_front_b' => $newman['front'][1],
                    'newman_front_c' => $newman['front'][2],
                    'newman_back_a' => $newman['back'][0],
                    'newman_back_b' => $newman['back'][1],
                    'newman_back_c' => $newman['back'][2],
                    'newman_rotation' => $newman['rotation_degrees'],
                    'newman_show_energy' => (int) $newman['show_energy_hint'],
                    'teacher_note' => $newman['teacher_note'],
                ];
            case 'reaction':
                $reaction = reaction_coordinate_template_registry::default_card_data('one_step_exergonic');
                return [
                    'name' => $reaction['title'],
                    'cardtype' => 'reaction_coordinate',
                    'rcd_template' => 'one_step_exergonic',
                    'rcd_points_json' => json_encode($reaction['points']),
                ];
            case 'orbital':
                $orbital = orbital_template_registry::get('ethene_pi_bond');
                return [
                    'name' => $orbital['label'],
                    'cardtype' => 'orbital_hybridization',
                    'orbital_template' => 'ethene_pi_bond',
                    'orbital_smiles' => $orbital['smiles_pattern'],
                    'orbital_atom_idx' => 0,
                    'teacher_note' => $orbital['teacher_note'],
                ];
            default:
                return null;
        }
    }

    /**
     * Return select options for a tool's curated starting points.
     *
     * @param string $id Tool id.
     * @return array
     */
    public static function get_preset_options(string $id): array {
        switch ($id) {
            case 'newman':
                $templates = newman_template_registry::get_all();
                break;
            case 'reaction':
                $templates = reaction_coordinate_template_registry::get_all();
                break;
            case 'orbital':
                $templates = orbital_template_registry::get_all();
                break;
            default:
                return [];
        }

        $options = [];
        foreach ($templates as $template) {
            $options[$template['id']] = $template['label'];
        }
        return $options;
    }

    /**
     * Return validated renderer data keyed by preset id.
     *
     * @param string $id Tool id.
     * @return array
     */
    public static function get_preview_presets(string $id): array {
        $presets = [];
        if ($id === 'newman') {
            foreach (newman_template_registry::get_all() as $template) {
                $presets[$template['id']] = newman_template_registry::default_card_data($template['id']);
            }
        } elseif ($id === 'reaction') {
            foreach (reaction_coordinate_template_registry::get_all() as $template) {
                $presets[$template['id']] =
                    reaction_coordinate_template_registry::default_card_data($template['id']);
            }
        } elseif ($id === 'orbital') {
            foreach (orbital_template_registry::get_all() as $template) {
                $presets[$template['id']] = [
                    'title' => $template['label'],
                    'template_id' => $template['id'],
                    'smiles' => $template['smiles_pattern'] ?? '',
                    'atom_idx' => 0,
                    'description' => $template['description'],
                    'teacher_note' => $template['teacher_note'] ?? '',
                ];
            }
        }
        return $presets;
    }
}
