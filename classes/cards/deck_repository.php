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

namespace local_chemillusion\cards;

/**
 * Persistence for study decks and their cards.
 *
 * Capability and ownership checks are enforced by callers (pages / external
 * functions); this class is the data layer only.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class deck_repository {

    /**
     * Create a new deck and return its id.
     *
     * @param int $userid
     * @param int|null $courseid
     * @param string $name
     * @param string $visibility private|course|site
     * @param string $source manual|pubchem|import
     * @return int
     */
    public static function create_deck($userid, $courseid, $name, $visibility = 'private', $source = 'manual') {
        global $DB;
        $now = time();
        return (int) $DB->insert_record('local_chemillusion_decks', (object) [
            'courseid'   => $courseid ? (int) $courseid : null,
            'userid'     => (int) $userid,
            'name'       => clean_param($name, PARAM_TEXT),
            'visibility' => clean_param($visibility, PARAM_ALPHA),
            'source'     => clean_param($source, PARAM_ALPHANUMEXT),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Append a card to a deck and return the card id.
     *
     * @param int $deckid
     * @param array $card Card array (already sanitised).
     * @return int
     */
    public static function add_card($deckid, array $card) {
        global $DB;
        $sortorder = (int) $DB->count_records('local_chemillusion_cards', ['deckid' => $deckid]);
        $payload = isset($card['molecule_payload']) && $card['molecule_payload'] !== null
            ? json_encode($card['molecule_payload']) : null;
        $id = (int) $DB->insert_record('local_chemillusion_cards', (object) [
            'deckid'           => (int) $deckid,
            'cardtype'         => $card['cardtype'],
            'prompt'           => $card['prompt'],
            'answer'           => $card['answer'],
            'molecule_payload' => $payload,
            'sortorder'        => $sortorder,
            'created_at'       => time(),
        ]);
        self::touch($deckid);
        return $id;
    }

    /**
     * Get a deck record or null.
     *
     * @param int $deckid
     * @return \stdClass|null
     */
    public static function get_deck($deckid) {
        global $DB;
        return $DB->get_record('local_chemillusion_decks', ['id' => $deckid]) ?: null;
    }

    /**
     * Get the cards for a deck, ordered.
     *
     * @param int $deckid
     * @return array
     */
    public static function get_cards($deckid) {
        global $DB;
        return array_values($DB->get_records('local_chemillusion_cards', ['deckid' => $deckid], 'sortorder ASC'));
    }

    /**
     * List decks visible to a user, optionally scoped to a course.
     *
     * @param int $userid
     * @param int|null $courseid
     * @return array
     */
    public static function list_decks($userid, $courseid = null) {
        global $DB;
        $params = [];
        $where = '(userid = :userid OR visibility <> :private)';
        $params['userid'] = (int) $userid;
        $params['private'] = 'private';
        if ($courseid !== null) {
            $where .= ' AND (courseid = :courseid OR courseid IS NULL)';
            $params['courseid'] = (int) $courseid;
        }
        return array_values($DB->get_records_select('local_chemillusion_decks', $where, $params, 'updated_at DESC'));
    }

    /**
     * Delete a deck and its cards.
     *
     * @param int $deckid
     * @return void
     */
    public static function delete_deck($deckid) {
        global $DB;
        $DB->delete_records('local_chemillusion_cards', ['deckid' => $deckid]);
        $DB->delete_records('local_chemillusion_decks', ['id' => $deckid]);
    }

    /**
     * Update the deck's updated_at timestamp.
     *
     * @param int $deckid
     * @return void
     */
    protected static function touch($deckid) {
        global $DB;
        $DB->set_field('local_chemillusion_decks', 'updated_at', time(), ['id' => $deckid]);
    }
}
