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
 * ChemIllusion landing/dashboard page.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Load Moodle config - works with symlinks
$moodleroot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2);
require($moodleroot . '/config.php');

$courseid = optional_param('courseid', 0, PARAM_INT);

require_login();
$context = context_system::instance();
require_capability('local/chemillusion:view', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/chemillusion/index.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'local_chemillusion'));
$PAGE->set_heading(get_string('dashboard_heading', 'local_chemillusion'));

$isteacher = has_capability('local/chemillusion:viewdashboard', $context);
if (!$isteacher && $courseid) {
    $isteacher = has_capability('local/chemillusion:managedecks', context_course::instance($courseid));
}

$output = $PAGE->get_renderer('local_chemillusion');
$page = new \local_chemillusion\output\dashboard_page($isteacher, $courseid ?: null);

echo $output->header();
echo $output->render($page);
echo $output->footer();
