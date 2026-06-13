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
 * Library hooks for local_chemillusion.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add a ChemIllusion entry to the global (flat) navigation.
 *
 * @param global_navigation $navigation
 * @return void
 */
function local_chemillusion_extend_navigation(global_navigation $navigation) {
    global $CFG;

    if (!isloggedin() || isguestuser()) {
        return;
    }
    $context = context_system::instance();
    if (!has_capability('local/chemillusion:view', $context)) {
        return;
    }

    $node = $navigation->add(
        get_string('nav_studytools', 'local_chemillusion'),
        new moodle_url('/local/chemillusion/index.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        'local_chemillusion',
        new pix_icon('i/science', '')
    );
    $node->showinflatnavigation = true;
}
