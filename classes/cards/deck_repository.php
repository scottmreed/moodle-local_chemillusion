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
 * Capability checks are enforced by callers (pages / external functions).
 * Ownership and visibility helpers live here so pages and services apply the
 * same data-access policy.
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
            'visibility' => self::normalize_visibility($visibility),
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
     * Whether a deck record is visible to the caller.
     *
     * @param \stdClass|null $deck Deck record from get_deck().
     * @param int $userid Current Moodle user id.
     * @param int|null $courseid Current course scope, if any.
     * @param bool $canmanage Whether caller has manage capability in the current context.
     * @return bool
     */
    public static function can_view_deck($deck, $userid, $courseid = null, $canmanage = false) {
        if (!$deck) {
            return false;
        }
        if ($canmanage || (int) $deck->userid === (int) $userid) {
            return true;
        }
        if ($deck->visibility === 'site') {
            return true;
        }
        if ($deck->visibility === 'course') {
            return $courseid !== null && (int) $deck->courseid === (int) $courseid;
        }
        return false;
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
        $where = '(userid = :userid OR visibility = :site';
        $params['userid'] = (int) $userid;
        $params['site'] = 'site';
        if ($courseid !== null) {
            $where .= ' OR (visibility = :coursevis AND courseid = :courseid)';
            $params['coursevis'] = 'course';
            $params['courseid'] = (int) $courseid;
        }
        $where .= ')';
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

    /**
     * Normalize deck visibility to the supported values.
     *
     * @param string $visibility
     * @return string
     */
    protected static function normalize_visibility($visibility) {
        $visibility = clean_param($visibility, PARAM_ALPHA);
        return in_array($visibility, ['private', 'course', 'site'], true) ? $visibility : 'private';
    }
}
