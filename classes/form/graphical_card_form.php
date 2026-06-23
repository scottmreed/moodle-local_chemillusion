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

        // Hidden card id for edits.
        $mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

        // Card name.
        $mform->addElement('text', 'name',
            get_string('card_name_label', 'local_chemillusion'), ['size' => 60]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        // Card type selector.
        $types = card_type_registry::get_enabled_types();
        $options = [];
        foreach ($types as $tid) {
            $options[$tid] = get_string('cardtype_' . $tid, 'local_chemillusion');
        }
        $mform->addElement('select', 'cardtype',
            get_string('card_type_label', 'local_chemillusion'), $options);
        $mform->setType('cardtype', PARAM_ALPHANUMEXT);

        // Teacher note (all types).
        $mform->addElement('textarea', 'teacher_note',
            get_string('card_teacher_note', 'local_chemillusion'),
            ['rows' => 3, 'cols' => 60]);
        $mform->setType('teacher_note', PARAM_TEXT);

        // --- Molecule / SMILES section (molecule card types).
        $mform->addElement('header', 'molecule_section', 'Molecule / SMILES');
        $mform->addElement('text', 'smiles', 'SMILES', ['size' => 60]);
        $mform->setType('smiles', PARAM_RAW);

        $mform->addElement('text', 'molecule_name', 'Molecule name', ['size' => 40]);
        $mform->setType('molecule_name', PARAM_TEXT);

        $mform->addElement('text', 'group_id', 'Functional group id', ['size' => 40]);
        $mform->setType('group_id', PARAM_ALPHANUMEXT);

        // --- Newman projection section.
        $mform->addElement('header', 'newman_section', 'Newman projection');
        $mform->addElement('text', 'newman_front_a', 'Front substituent A', ['size' => 10]);
        $mform->addElement('text', 'newman_front_b', 'Front substituent B', ['size' => 10]);
        $mform->addElement('text', 'newman_front_c', 'Front substituent C', ['size' => 10]);
        $mform->addElement('text', 'newman_back_a', 'Back substituent A', ['size' => 10]);
        $mform->addElement('text', 'newman_back_b', 'Back substituent B', ['size' => 10]);
        $mform->addElement('text', 'newman_back_c', 'Back substituent C', ['size' => 10]);
        $mform->addElement('text', 'newman_rotation', 'Back carbon rotation (degrees)', ['size' => 6]);
        $mform->addElement('checkbox', 'newman_show_energy', 'Show energy hint');
        foreach (['newman_front_a', 'newman_front_b', 'newman_front_c',
                  'newman_back_a', 'newman_back_b', 'newman_back_c'] as $f) {
            $mform->setType($f, PARAM_TEXT);
        }
        $mform->setType('newman_rotation', PARAM_INT);

        // --- Reaction coordinate section.
        $mform->addElement('header', 'rcd_section', 'Reaction coordinate diagram');
        $mform->addElement('text', 'rcd_template', 'Template id', ['size' => 40]);
        $mform->setType('rcd_template', PARAM_ALPHANUMEXT);
        $mform->addElement('textarea', 'rcd_points_json', 'Points JSON (advanced)', ['rows' => 6, 'cols' => 60]);
        $mform->setType('rcd_points_json', PARAM_RAW);

        // --- Orbital section.
        $mform->addElement('header', 'orbital_section', 'Orbital and hybridization');
        $mform->addElement('text', 'orbital_smiles', 'SMILES for orbital analysis', ['size' => 60]);
        $mform->setType('orbital_smiles', PARAM_RAW);
        $mform->addElement('text', 'orbital_atom_idx', 'Atom index (optional)', ['size' => 6]);
        $mform->setType('orbital_atom_idx', PARAM_INT);

        // --- Reagent section.
        $mform->addElement('header', 'reagent_section', 'Reagent');
        $mform->addElement('text', 'reagent_acronym', 'Acronym', ['size' => 20]);
        $mform->setType('reagent_acronym', PARAM_TEXT);
        $mform->addElement('text', 'reagent_full_name', 'Full name', ['size' => 60]);
        $mform->setType('reagent_full_name', PARAM_TEXT);
        $mform->addElement('text', 'reagent_role', 'Role', ['size' => 60]);
        $mform->setType('reagent_role', PARAM_TEXT);

        $this->add_action_buttons(true, get_string('card_save', 'local_chemillusion'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (empty($data['cardtype'])) {
            $errors['cardtype'] = get_string('error_invalidinput', 'local_chemillusion');
        }
        return $errors;
    }
}
