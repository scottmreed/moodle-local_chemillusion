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
 * Human-readable data-flow summary page.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Load Moodle config - works with symlinks
$moodleroot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2);
require($moodleroot . '/config.php');

require_login();
$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/chemillusion/privacy.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('privacy_page_heading', 'local_chemillusion'));
$PAGE->set_heading(get_string('privacy_page_heading', 'local_chemillusion'));

$mode = get_config('local_chemillusion', 'mode');
$data = [
    'intro'           => get_string('privacy_page_intro', 'local_chemillusion'),
    'mode'            => $mode,
    'pubchem'         => (bool) get_config('local_chemillusion', 'enable_pubchem'),
    'linking'         => (bool) get_config('local_chemillusion', 'enable_account_linking'),
    'conversion'      => (bool) get_config('local_chemillusion', 'enable_conversion_metadata'),
    'external_off'    => (bool) get_config('local_chemillusion', 'disable_external'),
    'minimal'         => (bool) get_config('local_chemillusion', 'minimal_mode'),
];

$output = $PAGE->get_renderer('local_chemillusion');
echo $output->header();
echo $output->render_from_template('local_chemillusion/privacy_summary', $data);
echo $output->footer();
