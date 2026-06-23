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
 * Graphical chemistry cards — browse and create.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/chemillusion:view', $context);

if (!(bool) get_config('local_chemillusion', 'enable_graphical_cards')) {
    redirect(new moodle_url('/local/chemillusion/index.php'));
}

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/chemillusion/graphical.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('graphical_cards_heading', 'local_chemillusion'));
$PAGE->set_heading(get_string('graphical_cards_heading', 'local_chemillusion'));

$cancreate = has_capability('local/chemillusion:createcards', $context);

$cards = $DB->get_records('local_chemillusion_cards',
    ['owneruserid' => $USER->id],
    'created_at DESC',
    'id, name, cardtype, created_at',
    0, 50
);

$output = $PAGE->get_renderer('local_chemillusion');
$page   = new \local_chemillusion\output\graphical_card_page(array_values($cards), $cancreate);

$PAGE->requires->js_call_amd('local_chemillusion/graphical_card_app', 'init');

echo $output->header();
echo $output->render($page);
echo $output->footer();
