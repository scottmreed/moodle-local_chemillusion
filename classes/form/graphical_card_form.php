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

namespace local_chemillusion\form;

use moodleform;
use local_chemillusion\cards\card_type_registry;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/**
 * Moodle form for creating or editing a graphical chemistry card.
 *
 * JavaScript enhances this form to show/hide sections per card type.
 * The PHP form deliberately includes all fields so progressive enhancement
 * is layered on top of a fully functional server-side form.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class graphical_card_form extends moodleform {

    protected function definition() {
        $mform = $this->_form;
        $toolid = $this->_customdata['toolid'] ?? '';
        $fixedcardtype = $this->_customdata['fixedcardtype'] ?? '';
        $presetoptions = $this->_customdata['presetoptions'] ?? [];
        $focused = $toolid !== '' && $fixedcardtype !== '';

        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'name',
            get_string('card_name_label', 'local_chemillusion'), ['size' => 60]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        if ($fixedcardtype !== '') {
            $mform->addElement('hidden', 'cardtype', $fixedcardtype);
            $mform->setType('cardtype', PARAM_ALPHANUMEXT);
        } else {
            $types = card_type_registry::get_enabled_types();
            $options = [];
            foreach ($types as $tid) {
                $options[$tid] = get_string('cardtype_' . $tid, 'local_chemillusion');
            }
            $mform->addElement('select', 'cardtype',
                get_string('card_type_label', 'local_chemillusion'), $options);
            $mform->setType('cardtype', PARAM_ALPHANUMEXT);
        }

        if (!$focused || $toolid === 'molecule') {
            $this->add_molecule_fields($focused);
        }
        if (!$focused || $toolid === 'newman') {
            $this->add_newman_fields($focused, $presetoptions);
        }
        if (!$focused || $toolid === 'reaction') {
            $this->add_reaction_fields($focused, $presetoptions);
        }
        if (!$focused || $toolid === 'orbital') {
            $this->add_orbital_fields($focused, $presetoptions);
        }
        if (!$focused || $fixedcardtype === 'reagent_card') {
            $this->add_reagent_fields();
        }

        if ($focused) {
            $mform->addElement('html',
                '<section class="local-chemillusion-editor-preview" data-region="diagram-preview"'
                . ' data-tool="' . s($toolid) . '" aria-live="polite">'
                . '<h3>' . get_string('card_preview', 'local_chemillusion') . '</h3>'
                . '<div class="local-chemillusion-editor-preview-canvas" data-region="diagram-preview-canvas"></div>'
                . '</section>');
        }

        $mform->addElement('header', 'advanced_section',
            get_string('diagram_editor_advanced', 'local_chemillusion'));
        $mform->setExpanded('advanced_section', false);

        if (!$focused || $toolid === 'molecule') {
            $mform->addElement('text', 'molecule_name',
                get_string('diagram_editor_molecule_name', 'local_chemillusion'), ['size' => 40]);
            $mform->setType('molecule_name', PARAM_TEXT);
            $mform->addElement('text', 'group_id',
                get_string('diagram_editor_group_id', 'local_chemillusion'), ['size' => 40]);
            $mform->setType('group_id', PARAM_ALPHANUMEXT);
        }
        if (!$focused || $toolid === 'reaction') {
            $mform->addElement('textarea', 'rcd_points_json',
                get_string('diagram_editor_points_json', 'local_chemillusion'), ['rows' => 6, 'cols' => 60]);
            $mform->setType('rcd_points_json', PARAM_RAW);
        }
        if (!$focused || $toolid === 'orbital') {
            $mform->addElement('text', 'orbital_atom_idx',
                get_string('diagram_editor_atom_index', 'local_chemillusion'), ['size' => 6]);
            $mform->setType('orbital_atom_idx', PARAM_INT);
        }

        $mform->addElement('textarea', 'teacher_note',
            get_string('card_teacher_note', 'local_chemillusion'),
            ['rows' => 3, 'cols' => 60]);
        $mform->setType('teacher_note', PARAM_TEXT);

        $this->add_action_buttons(true, get_string('card_save', 'local_chemillusion'));
    }

    /**
     * Add molecule inputs.
     *
     * @param bool $focused Whether this is a direct tool editor.
     */
    private function add_molecule_fields(bool $focused): void {
        $mform = $this->_form;
        $mform->addElement('header', 'molecule_section',
            get_string('diagram_tool_molecule', 'local_chemillusion'));
        $mform->addElement('text', 'smiles',
            get_string('diagram_editor_smiles', 'local_chemillusion'), ['size' => 60]);
        $mform->setType('smiles', PARAM_RAW);
        if ($focused) {
            $mform->addRule('smiles', null, 'required', null, 'client');
        }
    }

    /**
     * Add Newman projection inputs.
     *
     * @param bool $focused Whether this is a direct tool editor.
     * @param array $presetoptions Select options.
     */
    private function add_newman_fields(bool $focused, array $presetoptions): void {
        $mform = $this->_form;
        $mform->addElement('header', 'newman_section',
            get_string('diagram_tool_newman', 'local_chemillusion'));
        if ($focused) {
            $mform->addElement('select', 'newman_preset',
                get_string('diagram_editor_starting_example', 'local_chemillusion'), $presetoptions);
            $mform->setType('newman_preset', PARAM_ALPHANUMEXT);
        }

        $front = [];
        $back = [];
        foreach (['a' => 'A', 'b' => 'B', 'c' => 'C'] as $key => $label) {
            $front[] = $mform->createElement('text', 'newman_front_' . $key, $label, ['size' => 8]);
            $back[] = $mform->createElement('text', 'newman_back_' . $key, $label, ['size' => 8]);
            $mform->setType('newman_front_' . $key, PARAM_TEXT);
            $mform->setType('newman_back_' . $key, PARAM_TEXT);
        }
        $mform->addGroup($front, 'newman_front_group',
            get_string('newman_front', 'local_chemillusion'), ' ', false);
        $mform->addGroup($back, 'newman_back_group',
            get_string('newman_back', 'local_chemillusion'), ' ', false);

        $rotations = [];
        for ($degrees = 0; $degrees < 360; $degrees += 60) {
            $rotations[$degrees] = $degrees . '°';
        }
        $mform->addElement('select', 'newman_rotation',
            get_string('diagram_editor_rotation', 'local_chemillusion'), $rotations);
        $mform->setType('newman_rotation', PARAM_INT);
        $mform->addElement('checkbox', 'newman_show_energy',
            get_string('diagram_editor_energy_hint', 'local_chemillusion'));
    }

    /**
     * Add reaction-coordinate inputs.
     *
     * @param bool $focused Whether this is a direct tool editor.
     * @param array $presetoptions Select options.
     */
    private function add_reaction_fields(bool $focused, array $presetoptions): void {
        $mform = $this->_form;
        $mform->addElement('header', 'rcd_section',
            get_string('diagram_tool_reaction', 'local_chemillusion'));
        if ($focused) {
            $mform->addElement('select', 'rcd_template',
                get_string('rcd_template_label', 'local_chemillusion'), $presetoptions);
        } else {
            $mform->addElement('text', 'rcd_template',
                get_string('rcd_template_label', 'local_chemillusion'), ['size' => 40]);
        }
        $mform->setType('rcd_template', PARAM_ALPHANUMEXT);
    }

    /**
     * Add orbital inputs.
     *
     * @param bool $focused Whether this is a direct tool editor.
     * @param array $presetoptions Select options.
     */
    private function add_orbital_fields(bool $focused, array $presetoptions): void {
        $mform = $this->_form;
        $mform->addElement('header', 'orbital_section',
            get_string('diagram_tool_orbital', 'local_chemillusion'));
        if ($focused) {
            $mform->addElement('select', 'orbital_template',
                get_string('diagram_editor_starting_example', 'local_chemillusion'), $presetoptions);
            $mform->setType('orbital_template', PARAM_ALPHANUMEXT);
        }
        $mform->addElement('text', 'orbital_smiles',
            get_string('diagram_editor_orbital_smiles', 'local_chemillusion'), ['size' => 60]);
        $mform->setType('orbital_smiles', PARAM_RAW);
    }

    /**
     * Add reagent inputs used by the legacy generic editor.
     */
    private function add_reagent_fields(): void {
        $mform = $this->_form;
        $mform->addElement('header', 'reagent_section', 'Reagent');
        $mform->addElement('text', 'reagent_acronym', 'Acronym', ['size' => 20]);
        $mform->setType('reagent_acronym', PARAM_TEXT);
        $mform->addElement('text', 'reagent_full_name', 'Full name', ['size' => 60]);
        $mform->setType('reagent_full_name', PARAM_TEXT);
        $mform->addElement('text', 'reagent_role', 'Role', ['size' => 60]);
        $mform->setType('reagent_role', PARAM_TEXT);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (empty($data['cardtype'])) {
            $errors['cardtype'] = get_string('error_invalidinput', 'local_chemillusion');
        }
        if (($data['cardtype'] ?? '') === 'reaction_coordinate' && !empty($data['rcd_points_json'])) {
            json_decode($data['rcd_points_json'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors['rcd_points_json'] = get_string('diagram_editor_invalid_json', 'local_chemillusion');
            }
        }
        return $errors;
    }
}
