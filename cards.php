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
 * Study deck list and flashcard player.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

$deckid = optional_param('deckid', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);

require_login();
$context = context_system::instance();
require_capability('local/chemillusion:view', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/chemillusion/cards.php', $deckid ? ['deckid' => $deckid] : []));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('cards_heading', 'local_chemillusion'));
$PAGE->set_heading(get_string('cards_heading', 'local_chemillusion'));

$output = $PAGE->get_renderer('local_chemillusion');
echo $output->header();

if ($deckid) {
    $deck = \local_chemillusion\cards\deck_repository::get_deck($deckid);
    if (!$deck) {
        throw new \moodle_exception('error_nomatch', 'local_chemillusion');
    }
    $cards = [];
    foreach (\local_chemillusion\cards\deck_repository::get_cards($deckid) as $c) {
        $cards[] = [
            'cardtype' => $c->cardtype,
            'prompt'   => $c->prompt,
            'answer'   => $c->answer,
        ];
    }
    \local_chemillusion\telemetry\local_event_logger::log(
        \local_chemillusion\telemetry\local_event_logger::EVENT_STUDY_SESSION, 'study_deck');

    echo \html_writer::tag('h3', format_string($deck->name));
    echo \html_writer::div('', 'local-chemillusion-flashcards', [
        'id' => 'local-chemillusion-flashcards', 'tabindex' => '0',
        'aria-label' => get_string('cards_heading', 'local_chemillusion'),
    ]);
    $PAGE->requires->js_call_amd('local_chemillusion/flashcard_player', 'init',
        [['cards' => $cards]]);
} else {
    $cancreate = has_capability('local/chemillusion:managedecks', $context)
        || ($courseid && has_capability('local/chemillusion:managedecks', context_course::instance($courseid)));
    $page = new \local_chemillusion\output\study_deck_page($USER->id, $courseid ?: null, $cancreate);
    echo $output->render($page);
}

echo $output->footer();
