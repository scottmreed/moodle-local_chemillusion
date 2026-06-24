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

namespace local_chemillusion\phpunit;

use local_chemillusion\cards\diagram_tool_catalog;

/**
 * Tests for the root-page diagram tool catalog.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @coversDefaultClass \local_chemillusion\cards\diagram_tool_catalog
 */
final class diagram_tool_catalog_test extends \advanced_testcase {
    /**
     * Catalog lists four diagram tools in display order.
     */
    public function test_catalog_has_four_tools_in_creation_order(): void {
        $tools = diagram_tool_catalog::get_all(false);

        $this->assertSame(
            ['molecule', 'newman', 'reaction', 'orbital'],
            array_column($tools, 'id')
        );
        foreach ($tools as $tool) {
            $this->assertArrayHasKey('cardtype', $tool);
            $this->assertArrayHasKey('titlekey', $tool);
            $this->assertArrayHasKey('descriptionkey', $tool);
            $this->assertArrayHasKey('illustration', $tool);
            $this->assertArrayHasKey('imagealtkey', $tool);
            $this->assertSame(['tool' => $tool['id']], $tool['editorparams']);
        }
    }

    /**
     * Each tool exposes sensible editor defaults.
     */
    public function test_each_tool_has_a_useful_default(): void {
        $this->assertSame(
            'ClCCCCBr',
            diagram_tool_catalog::get_editor_defaults('molecule')['smiles']
        );
        $this->assertSame(
            'butane_anti',
            diagram_tool_catalog::get_editor_defaults('newman')['newman_preset']
        );
        $this->assertSame(
            'one_step_exergonic',
            diagram_tool_catalog::get_editor_defaults('reaction')['rcd_template']
        );
        $this->assertSame(
            'ethene_pi_bond',
            diagram_tool_catalog::get_editor_defaults('orbital')['orbital_template']
        );
    }

    /**
     * Unknown tool ids resolve to null.
     */
    public function test_unknown_tool_returns_null(): void {
        $this->assertNull(diagram_tool_catalog::get('unknown', false));
        $this->assertNull(diagram_tool_catalog::get_editor_defaults('unknown'));
    }

    /**
     * Defaults include enough data for immediate preview.
     */
    public function test_defaults_are_complete_for_immediate_preview(): void {
        $reaction = diagram_tool_catalog::get_editor_defaults('reaction');
        $this->assertCount(3, json_decode($reaction['rcd_points_json'], true));

        $orbital = diagram_tool_catalog::get_editor_defaults('orbital');
        $this->assertSame('C=C', $orbital['orbital_smiles']);
        $this->assertSame('ethene_pi_bond', $orbital['orbital_template']);

        $newman = diagram_tool_catalog::get_editor_defaults('newman');
        $this->assertSame(['CH3', 'H', 'H'], [
            $newman['newman_front_a'],
            $newman['newman_front_b'],
            $newman['newman_front_c'],
        ]);
    }

    /**
     * Preset options come from the existing template registries.
     */
    public function test_preset_options_use_existing_registries(): void {
        $this->assertArrayHasKey('butane_anti', diagram_tool_catalog::get_preset_options('newman'));
        $this->assertArrayHasKey('one_step_exergonic', diagram_tool_catalog::get_preset_options('reaction'));
        $this->assertArrayHasKey('ethene_pi_bond', diagram_tool_catalog::get_preset_options('orbital'));
        $this->assertSame([], diagram_tool_catalog::get_preset_options('molecule'));
    }

    /**
     * Card types map back to focused diagram tools.
     */
    public function test_card_types_map_back_to_focused_tools(): void {
        $this->assertSame('molecule', diagram_tool_catalog::get_by_cardtype('smiles_to_structure')['id']);
        $this->assertSame('newman', diagram_tool_catalog::get_by_cardtype('newman_projection')['id']);
        $this->assertSame('reaction', diagram_tool_catalog::get_by_cardtype('reaction_coordinate')['id']);
        $this->assertSame('orbital', diagram_tool_catalog::get_by_cardtype('orbital_hybridization')['id']);
        $this->assertNull(diagram_tool_catalog::get_by_cardtype('reagent_card'));
    }

    /**
     * Preview presets match renderer-ready payload shapes.
     */
    public function test_preview_presets_have_renderer_ready_shapes(): void {
        $newman = diagram_tool_catalog::get_preview_presets('newman');
        $this->assertSame(180, $newman['butane_anti']['rotation_degrees']);

        $reaction = diagram_tool_catalog::get_preview_presets('reaction');
        $this->assertCount(3, $reaction['one_step_exergonic']['points']);

        $orbital = diagram_tool_catalog::get_preview_presets('orbital');
        $this->assertSame('ethene_pi_bond', $orbital['ethene_pi_bond']['template_id']);
        $this->assertSame([], diagram_tool_catalog::get_preview_presets('molecule'));
    }
}
