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

use local_chemillusion\output\dashboard_page;

/**
 * Dashboard diagram gallery tests.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @coversDefaultClass \local_chemillusion\output\dashboard_page
 */
final class dashboard_page_test extends \advanced_testcase {
    public function test_admin_sees_four_direct_diagram_tools(): void {
        global $PAGE;

        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('enable_graphical_cards', 1, 'local_chemillusion');
        set_config('enable_newman_cards', 1, 'local_chemillusion');
        set_config('enable_reaction_coordinate_cards', 1, 'local_chemillusion');
        set_config('enable_orbital_cards', 1, 'local_chemillusion');

        $PAGE->set_url('/');
        $renderer = $PAGE->get_renderer('local_chemillusion');
        $data = (new dashboard_page(true))->export_for_template($renderer);

        $this->assertTrue($data['has_diagram_tools']);
        $this->assertCount(4, $data['diagram_tools']);
        $this->assertSame(
            ['molecule', 'newman', 'reaction', 'orbital'],
            array_column($data['diagram_tools'], 'id')
        );
        foreach ($data['diagram_tools'] as $tool) {
            $this->assertStringContainsString(
                '/local/chemillusion/card_edit.php?tool=' . $tool['id'],
                $tool['url']
            );
        }
    }

    /**
     * Test gallery is hidden when graphical cards are disabled.
     */
    public function test_gallery_is_hidden_when_graphical_cards_are_disabled(): void {
        global $PAGE;

        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('enable_graphical_cards', 0, 'local_chemillusion');

        $PAGE->set_url('/');
        $renderer = $PAGE->get_renderer('local_chemillusion');
        $data = (new dashboard_page(true))->export_for_template($renderer);

        $this->assertFalse($data['has_diagram_tools']);
        $this->assertSame([], $data['diagram_tools']);
    }

    /**
     * Test dashboard template renders tool gallery markup.
     */
    public function test_dashboard_template_renders_tool_gallery_markup(): void {
        global $PAGE;

        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('enable_graphical_cards', 1, 'local_chemillusion');
        set_config('enable_newman_cards', 1, 'local_chemillusion');
        set_config('enable_reaction_coordinate_cards', 1, 'local_chemillusion');
        set_config('enable_orbital_cards', 1, 'local_chemillusion');

        $PAGE->set_url('/');
        $renderer = $PAGE->get_renderer('local_chemillusion');
        $html = $renderer->render_dashboard_page(new dashboard_page(true));

        $this->assertStringContainsString('local-chemillusion-diagram-tool-grid', $html);
        $this->assertStringContainsString('card_edit.php?tool=molecule', $html);
        $this->assertStringContainsString('card_edit.php?tool=orbital', $html);
        $this->assertStringContainsString('&pi;', $html);
        $this->assertStringContainsString('&sigma;', $html);
    }
}
