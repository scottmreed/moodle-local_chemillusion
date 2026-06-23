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
 * Graphical card student/preview view.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$moodleroot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2);
require($moodleroot . '/config.php');

$cardid = required_param('id', PARAM_INT);

require_login();
$context = context_system::instance();
require_capability('local/chemillusion:view', $context);

$card = $DB->get_record('local_chemillusion_cards', ['id' => $cardid], '*', MUST_EXIST);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/chemillusion/card_view.php', ['id' => $cardid]));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(format_string($card->name ?? get_string('card_view_heading', 'local_chemillusion')));
$PAGE->set_heading(format_string($card->name ?? get_string('card_view_heading', 'local_chemillusion')));

$canexport = has_capability('local/chemillusion:exportcards', $context);
$canedit   = ($card->owneruserid == $USER->id)
    ? has_capability('local/chemillusion:editowncards', $context)
    : has_capability('local/chemillusion:editallcards', $context);

$output  = $PAGE->get_renderer('local_chemillusion');
$preview = new \local_chemillusion\output\graphical_card_preview($card, $canexport, $canedit);

$PAGE->requires->js_call_amd('local_chemillusion/graphical_card_app', 'initView', [['cardid' => $cardid]]);

echo $output->header();
echo $output->render($preview);
echo $output->footer();
