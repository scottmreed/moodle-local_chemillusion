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

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/formslib.php');

/**
 * Simple molecule lookup form (progressive enhancement; JS adds live lookup).
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class molecule_lookup_form extends \moodleform {
    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement(
            'text',
            'query',
            get_string('lookup_label', 'local_chemillusion'),
            [
                'size' => 48,
                'placeholder' => get_string('lookup_placeholder', 'local_chemillusion'),
            ]
        );
        $mform->setType('query', PARAM_TEXT);
        $mform->addRule('query', get_string('error_invalidinput', 'local_chemillusion'), 'required', null, 'client');

        $this->add_action_buttons(false, get_string('lookup_button', 'local_chemillusion'));
    }
}
