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

namespace local_chemillusion\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use context;
use context_system;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider for local_chemillusion.
 *
 * Stores user-linked data only in the system context: account-link mappings,
 * user-created study decks and cards, and (unless minimal mode) coarse events.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Describe the data this plugin stores.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_chemillusion_links', [
            'userid'                  => 'privacy:metadata:local_chemillusion_links:userid',
            'chemillusion_user_id'    => 'privacy:metadata:local_chemillusion_links:chemillusion_user_id',
            'chemillusion_email_hash' => 'privacy:metadata:local_chemillusion_links:chemillusion_email_hash',
            'linked_at'               => 'privacy:metadata:local_chemillusion_links:linked_at',
            'last_launch_at'          => 'privacy:metadata:local_chemillusion_links:last_launch_at',
        ], 'privacy:metadata:local_chemillusion_links');

        $collection->add_database_table('local_chemillusion_decks', [
            'userid'     => 'privacy:metadata:local_chemillusion_decks:userid',
            'name'       => 'privacy:metadata:local_chemillusion_decks:name',
            'created_at' => 'privacy:metadata:local_chemillusion_decks:created_at',
        ], 'privacy:metadata:local_chemillusion_decks');

        $collection->add_database_table('local_chemillusion_cards', [
            'prompt' => 'privacy:metadata:local_chemillusion_cards:prompt',
            'answer' => 'privacy:metadata:local_chemillusion_cards:answer',
        ], 'privacy:metadata:local_chemillusion_cards');

        $collection->add_external_location_link('chemillusion_saas', [
            'source'  => 'privacy:metadata:chemillusion_saas:source',
            'role'    => 'privacy:metadata:chemillusion_saas:role',
            'surface' => 'privacy:metadata:chemillusion_saas:surface',
        ], 'privacy:metadata:chemillusion_saas');

        $collection->add_database_table('local_chemillusion_events', [
            'userid'     => 'privacy:metadata:local_chemillusion_events:userid',
            'courseid'   => 'privacy:metadata:local_chemillusion_events:courseid',
            'eventname'  => 'privacy:metadata:local_chemillusion_events:eventname',
            'surface'    => 'privacy:metadata:local_chemillusion_events:surface',
            'cta'        => 'privacy:metadata:local_chemillusion_events:cta',
            'payload'    => 'privacy:metadata:local_chemillusion_events:payload',
            'created_at' => 'privacy:metadata:local_chemillusion_events:created_at',
        ], 'privacy:metadata:local_chemillusion_events');

        $collection->add_external_location_link('pubchem_pug_rest', [
            'identifier' => 'privacy:metadata:pubchem_pug_rest:identifier',
        ], 'privacy:metadata:pubchem_pug_rest');

        return $collection;
    }

    /**
     * Contexts holding data for a user.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;
        $contextlist = new contextlist();
        $has = $DB->record_exists('local_chemillusion_links', ['userid' => $userid])
            || $DB->record_exists('local_chemillusion_decks', ['userid' => $userid])
            || $DB->record_exists('local_chemillusion_events', ['userid' => $userid]);
        if ($has) {
            $contextlist->add_system_context();
        }
        return $contextlist;
    }

    /**
     * Users within a context.
     *
     * @param userlist $userlist
     * @return void
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if (!$context instanceof context_system) {
            return;
        }
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_chemillusion_links}', []);
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_chemillusion_decks}', []);
        $userlist->add_from_sql(
            'userid',
            'SELECT userid FROM {local_chemillusion_events} WHERE userid IS NOT NULL',
            []
        );
    }

    /**
     * Export a user's data.
     *
     * @param approved_contextlist $contextlist
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_system) {
                continue;
            }
            $links = $DB->get_records('local_chemillusion_links', ['userid' => $userid]);
            if ($links) {
                writer::with_context($context)->export_data(
                    [get_string('pluginname', 'local_chemillusion'), 'links'],
                    (object) ['links' => array_values($links)]
                );
            }
            $decks = $DB->get_records('local_chemillusion_decks', ['userid' => $userid]);
            foreach ($decks as $deck) {
                $cards = $DB->get_records('local_chemillusion_cards', ['deckid' => $deck->id]);
                $deck->cards = array_values($cards);
                writer::with_context($context)->export_data(
                    [get_string('pluginname', 'local_chemillusion'), 'decks', $deck->id],
                    $deck
                );
            }
            $events = $DB->get_records('local_chemillusion_events', ['userid' => $userid], 'created_at ASC');
            if ($events) {
                writer::with_context($context)->export_data(
                    [get_string('pluginname', 'local_chemillusion'), 'events'],
                    (object) ['events' => array_values($events)]
                );
            }
        }
    }

    /**
     * Delete all plugin user data in a context.
     *
     * @param context $context
     * @return void
     */
    public static function delete_data_for_all_users_in_context(context $context) {
        global $DB;
        if (!$context instanceof context_system) {
            return;
        }
        $deckids = $DB->get_fieldset_select('local_chemillusion_decks', 'id', '1=1', []);
        if ($deckids) {
            $DB->delete_records_list('local_chemillusion_cards', 'deckid', $deckids);
        }
        $DB->delete_records('local_chemillusion_decks', []);
        $DB->delete_records('local_chemillusion_links', []);
        $DB->delete_records('local_chemillusion_events', []);
    }

    /**
     * Delete data for one user.
     *
     * @param approved_contextlist $contextlist
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_system) {
                continue;
            }
            self::delete_for_userids([$userid]);
        }
    }

    /**
     * Delete data for a set of users in a context.
     *
     * @param approved_userlist $userlist
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        if (!$userlist->get_context() instanceof context_system) {
            return;
        }
        self::delete_for_userids($userlist->get_userids());
    }

    /**
     * Helper: remove links/decks/cards/events for the given user ids.
     *
     * @param array $userids
     * @return void
     */
    protected static function delete_for_userids(array $userids) {
        global $DB;
        if (empty($userids)) {
            return;
        }
        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $deckids = $DB->get_fieldset_select('local_chemillusion_decks', 'id', "userid $insql", $params);
        if ($deckids) {
            $DB->delete_records_list('local_chemillusion_cards', 'deckid', $deckids);
        }
        $DB->delete_records_select('local_chemillusion_decks', "userid $insql", $params);
        $DB->delete_records_select('local_chemillusion_links', "userid $insql", $params);
        $DB->delete_records_select('local_chemillusion_events', "userid $insql", $params);
    }
}
