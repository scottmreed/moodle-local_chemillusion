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

namespace local_chemillusion\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Plugin renderer for local_chemillusion.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends \plugin_renderer_base {

    /**
     * Render the dashboard/landing page.
     *
     * @param dashboard_page $page
     * @return string
     */
    public function render_dashboard_page(dashboard_page $page) {
        return $this->render_from_template('local_chemillusion/dashboard', $page->export_for_template($this));
    }

    /**
     * Render a molecule result card.
     *
     * @param molecule_card $card
     * @return string
     */
    public function render_molecule_card(molecule_card $card) {
        return $this->render_from_template('local_chemillusion/molecule_result_card', $card->export_for_template($this));
    }

    /**
     * Render the study deck page.
     *
     * @param study_deck_page $page
     * @return string
     */
    public function render_study_deck_page(study_deck_page $page) {
        return $this->render_from_template('local_chemillusion/study_deck', $page->export_for_template($this));
    }
}
