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
 * Molecule lookup and study tools page (Phase 1A server-side + Phase 1B JS).
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
require_capability('local/chemillusion:view', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/chemillusion/tools.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('tools_heading', 'local_chemillusion'));
$PAGE->set_heading(get_string('tools_heading', 'local_chemillusion'));

$output = $PAGE->get_renderer('local_chemillusion');

$form = new \local_chemillusion\form\molecule_lookup_form($PAGE->url->out(false));

echo $output->header();

// Navigation breadcrumb.
echo \html_writer::tag('nav',
    \html_writer::tag('a', get_string('back_to_dashboard', 'local_chemillusion'),
        ['href' => (new moodle_url('/local/chemillusion/'))->out(false), 'class' => 'btn btn-sm btn-outline-secondary mb-3']),
    ['class' => 'local-chemillusion-breadcrumb']);

// Progressive enhancement: server-side lookup when JS is unavailable.
$rendered = '';
if ($data = $form->get_data()) {
    $result = \local_chemillusion\api\pubchem_client::resolve($data->query);
    if ($result['status'] === 'ok') {
        \local_chemillusion\telemetry\local_event_logger::log(
            \local_chemillusion\telemetry\local_event_logger::EVENT_LOOKUP, 'molecule_lookup');
        $card = new \local_chemillusion\output\molecule_card($result['data']);
        $rendered = $output->render($card);
    } else {
        $rendered = $output->notification(
            get_string('error_' . $result['error'], 'local_chemillusion'), 'warning');
    }
}

$form->display();
echo \html_writer::div('', '', ['id' => 'local-chemillusion-results', 'aria-live' => 'polite']);
echo $rendered;

// Phase 1B configuration and live lookup wiring.
$rdkitenabled = (bool) get_config('local_chemillusion', 'enable_rdkit');
$config = [
    'rdkitEnabled'  => $rdkitenabled,
    'rdkitJsUrl'    => (new moodle_url('/local/chemillusion/thirdparty/rdkit-js/RDKit_minimal.js'))->out(false),
    'rdkitWasmUrl'  => (new moodle_url('/local/chemillusion/thirdparty/rdkit-js/RDKit_minimal.wasm'))->out(false),
    'smarts'        => \local_chemillusion\cards\functional_group_dictionary::smarts_registry(),
    'launchUrl'     => (new moodle_url('/local/chemillusion/launch.php'))->out(false),
    'visualEnabled' => (bool) get_config('local_chemillusion', 'enable_visual_preview'),
    'sesskey'       => sesskey(),
];
$PAGE->requires->js_call_amd('local_chemillusion/molecule_lookup', 'init', [$config]);

echo $output->footer();
