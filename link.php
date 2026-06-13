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
 * Start or complete a ChemIllusion account link.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

$action = optional_param('action', 'view', PARAM_ALPHA);
$token = optional_param('token', '', PARAM_RAW);

require_login();
$context = context_system::instance();
require_capability('local/chemillusion:link', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/chemillusion/link.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('link_heading', 'local_chemillusion'));
$PAGE->set_heading(get_string('link_heading', 'local_chemillusion'));

$output = $PAGE->get_renderer('local_chemillusion');

if (!\local_chemillusion\auth\account_linker::is_enabled()) {
    echo $output->header();
    echo $output->notification(get_string('link_disabled', 'local_chemillusion'), 'info');
    echo $output->footer();
    die();
}

$role = has_capability('local/chemillusion:managedecks', $context) ? 'teacher' : 'student';

if ($action === 'start') {
    require_sesskey();
    \local_chemillusion\telemetry\local_event_logger::log(
        \local_chemillusion\telemetry\local_event_logger::EVENT_LINK_CLICK, 'account_link', 'account_link');
    $url = \local_chemillusion\auth\account_linker::start_link($USER->id, $role, 'account_link');
    redirect(new moodle_url($url));
}

if ($action === 'complete' && $token !== '') {
    $ok = \local_chemillusion\auth\account_linker::complete_link($USER->id, $token);
    echo $output->header();
    if ($ok) {
        echo $output->notification(get_string('link_success', 'local_chemillusion'), 'success');
    } else {
        echo $output->notification(get_string('error_invalidsesskey', 'local_chemillusion'), 'error');
    }
    echo $output->footer();
    die();
}

// Default view: show the connect button.
echo $output->header();
$linked = \local_chemillusion\auth\account_linker::is_linked($USER->id);
if ($linked) {
    echo $output->notification(get_string('link_status_linked', 'local_chemillusion'), 'success');
} else {
    $starturl = new moodle_url('/local/chemillusion/link.php', ['action' => 'start', 'sesskey' => sesskey()]);
    echo \html_writer::link($starturl, get_string('link_start', 'local_chemillusion'),
        ['class' => 'btn btn-primary']);
}
echo $output->footer();
