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

/**
 * Tests for graphical_card_form focused editor fields.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chemillusion\phpunit;

use local_chemillusion\cards\diagram_tool_catalog;
use local_chemillusion\form\graphical_card_form;

/**
 * Exposes QuickForm for focused editor assertions.
 *
 * @package    local_chemillusion
 *
 * @coversDefaultClass \local_chemillusion\form\graphical_card_form
 */
final class testable_graphical_card_form extends graphical_card_form {
    /**
     * Return the underlying QuickForm instance.
     *
     * @return \MoodleQuickForm
     */
    public function get_quickform(): \MoodleQuickForm {
        return $this->_form;
    }
}

/**
 * Focused graphical-card form tests.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class graphical_card_form_test extends \advanced_testcase {
    /**
     * Build a direct tool form.
     *
     * @param string $toolid Tool id.
     * @return \MoodleQuickForm
     */
    private function build_form(string $toolid): \MoodleQuickForm {
        $tool = diagram_tool_catalog::get($toolid, false);
        $form = new testable_graphical_card_form(null, [
            'toolid' => $toolid,
            'fixedcardtype' => $tool['cardtype'],
            'presetoptions' => diagram_tool_catalog::get_preset_options($toolid),
        ]);
        return $form->get_quickform();
    }

    public function test_molecule_form_only_asks_for_molecule_inputs_up_front(): void {
        $form = $this->build_form('molecule');

        $this->assertTrue($form->elementExists('smiles'));
        $this->assertTrue($form->elementExists('molecule_name'));
        $this->assertFalse($form->elementExists('newman_preset'));
        $this->assertFalse($form->elementExists('rcd_template'));
        $this->assertFalse($form->elementExists('orbital_template'));
    }

    public function test_newman_form_has_a_starting_example_and_grouped_substituents(): void {
        $form = $this->build_form('newman');

        $this->assertTrue($form->elementExists('newman_preset'));
        $this->assertTrue($form->elementExists('newman_front_group'));
        $this->assertTrue($form->elementExists('newman_back_group'));
        $this->assertFalse($form->elementExists('smiles'));
    }

    public function test_reaction_and_orbital_forms_use_curated_examples(): void {
        $reaction = $this->build_form('reaction');
        $orbital = $this->build_form('orbital');

        $this->assertTrue($reaction->elementExists('rcd_template'));
        $this->assertTrue($reaction->elementExists('rcd_points_json'));
        $this->assertTrue($orbital->elementExists('orbital_template'));
        $this->assertTrue($orbital->elementExists('orbital_smiles'));
        $this->assertTrue($orbital->elementExists('orbital_atom_idx'));
    }
}
