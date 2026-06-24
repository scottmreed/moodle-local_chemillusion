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

namespace local_chemillusion\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use local_chemillusion\cards\card_generator;
use local_chemillusion\cards\deck_repository;
use local_chemillusion\telemetry\local_event_logger;

defined('MOODLE_INTERNAL') || die();

/**
 * AJAX endpoint: create a local study deck and its cards.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_deck extends external_api {
    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'name'     => new external_value(PARAM_TEXT, 'Deck name'),
            'courseid' => new external_value(PARAM_INT, 'Course id, or 0 for site context', VALUE_DEFAULT, 0),
            'visibility' => new external_value(PARAM_ALPHA, 'private|course|site', VALUE_DEFAULT, 'private'),
            'cards'    => new external_multiple_structure(new external_single_structure([
                'cardtype'         => new external_value(PARAM_ALPHANUMEXT, 'Card type'),
                'prompt'           => new external_value(PARAM_TEXT, 'Front'),
                'answer'           => new external_value(PARAM_TEXT, 'Back'),
                'molecule_payload' => new external_value(PARAM_RAW, 'JSON-encoded molecule context', VALUE_OPTIONAL),
            ])),
        ]);
    }

    /**
     * Create the deck.
     *
     * @param string $name
     * @param int $courseid
     * @param string $visibility
     * @param array $cards
     * @return array
     */
    public static function execute($name, $courseid, $visibility, $cards) {
        global $USER;
        $params = self::validate_parameters(self::execute_parameters(), [
            'name' => $name, 'courseid' => $courseid, 'visibility' => $visibility, 'cards' => $cards,
        ]);

        if ($params['courseid']) {
            $context = \context_course::instance($params['courseid']);
        } else {
            $context = \context_system::instance();
        }
        self::validate_context($context);
        require_capability('local/chemillusion:managedecks', $context);

        $deckid = deck_repository::create_deck(
            $USER->id,
            $params['courseid'] ?: null,
            $params['name'],
            $params['visibility'],
            'manual'
        );

        $count = 0;
        foreach ($params['cards'] as $card) {
            if (isset($card['molecule_payload']) && is_string($card['molecule_payload'])) {
                $decoded = json_decode($card['molecule_payload'], true);
                $card['molecule_payload'] = is_array($decoded) ? $decoded : null;
            }
            deck_repository::add_card($deckid, card_generator::sanitize($card));
            $count++;
        }

        local_event_logger::log(local_event_logger::EVENT_DECK_CREATED, 'study_deck', null, $params['courseid'] ?: null);

        return ['deckid' => $deckid, 'cardcount' => $count];
    }

    /**
     * Return description.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'deckid'    => new external_value(PARAM_INT, 'New deck id'),
            'cardcount' => new external_value(PARAM_INT, 'Number of cards stored'),
        ]);
    }
}
