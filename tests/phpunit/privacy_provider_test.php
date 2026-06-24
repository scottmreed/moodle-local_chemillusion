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

namespace local_chemillusion\phpunit;

use local_chemillusion\privacy\provider;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;

/**
 * Unit tests for the privacy provider.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_chemillusion\privacy\provider
 */
final class privacy_provider_test extends \advanced_testcase {
    public function test_get_metadata_is_populated(): void {
        $this->resetAfterTest();
        $collection = provider::get_metadata(new collection('local_chemillusion'));
        $this->assertNotEmpty($collection->get_collection());
    }

    public function test_metadata_includes_events_table(): void {
        $this->resetAfterTest();
        $collection = provider::get_metadata(new collection('local_chemillusion'));
        $items = $collection->get_collection();
        $names = array_map(fn($item) => $item->get_name(), $items);
        $this->assertContains(
            'local_chemillusion_events',
            $names,
            'Privacy metadata must declare local_chemillusion_events'
        );
    }

    public function test_metadata_includes_pubchem_external_location(): void {
        $this->resetAfterTest();
        $collection = provider::get_metadata(new collection('local_chemillusion'));
        $items = $collection->get_collection();
        $names = array_map(fn($item) => $item->get_name(), $items);
        $this->assertContains(
            'pubchem_pug_rest',
            $names,
            'Privacy metadata must declare pubchem_pug_rest external location'
        );
    }

    public function test_events_create_context_for_userid(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $system = \context_system::instance();

        $DB->insert_record('local_chemillusion_events', (object) [
            'userid'    => $user->id,
            'courseid'  => 0,
            'eventname' => 'molecule_lookup',
            'surface'   => 'tools',
            'cta'       => null,
            'payload'   => null,
            'created_at' => time(),
        ]);

        $contexts = provider::get_contexts_for_userid($user->id);
        $this->assertContains(
            $system->id,
            $contexts->get_contextids(),
            'An event row with a userid must create a system context entry'
        );
    }

    public function test_contexts_export_and_delete(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $system = \context_system::instance();

        $DB->insert_record(
            'local_chemillusion_links',
            (object) ['userid' => $user->id, 'linked_at' => time(), 'status' => 'linked']
        );
        $deckid = $DB->insert_record('local_chemillusion_decks', (object) [
            'userid' => $user->id, 'name' => 'D', 'visibility' => 'private',
            'source' => 'manual', 'created_at' => time(), 'updated_at' => time(),
        ]);
        $DB->insert_record('local_chemillusion_cards', (object) [
            'deckid' => $deckid, 'cardtype' => 'molecule_identity', 'prompt' => 'p',
            'answer' => 'a', 'sortorder' => 0, 'created_at' => time(),
        ]);
        $DB->insert_record('local_chemillusion_events', (object) [
            'userid'    => $user->id,
            'courseid'  => 0,
            'eventname' => 'molecule_lookup',
            'surface'   => 'tools',
            'cta'       => null,
            'payload'   => null,
            'created_at' => time(),
        ]);

        $contexts = provider::get_contexts_for_userid($user->id);
        $this->assertContains($system->id, $contexts->get_contextids());

        $approved = new approved_contextlist($user, 'local_chemillusion', [$system->id]);
        provider::export_user_data($approved);
        $this->assertTrue(writer::with_context($system)->has_any_data());

        provider::delete_data_for_user($approved);
        $this->assertFalse($DB->record_exists('local_chemillusion_links', ['userid' => $user->id]));
        $this->assertFalse($DB->record_exists('local_chemillusion_decks', ['userid' => $user->id]));
        $this->assertFalse($DB->record_exists('local_chemillusion_cards', ['deckid' => $deckid]));
        $this->assertFalse(
            $DB->record_exists('local_chemillusion_events', ['userid' => $user->id]),
            'Deleting user data must remove their event rows'
        );
    }

    public function test_delete_user_does_not_affect_other_users_events(): void {
        global $DB;
        $this->resetAfterTest();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $system = \context_system::instance();

        foreach ([$user1->id, $user2->id] as $uid) {
            $DB->insert_record('local_chemillusion_events', (object) [
                'userid'    => $uid,
                'courseid'  => 0,
                'eventname' => 'molecule_lookup',
                'surface'   => 'tools',
                'cta'       => null,
                'payload'   => null,
                'created_at' => time(),
            ]);
        }

        $approved = new approved_contextlist($user1, 'local_chemillusion', [$system->id]);
        provider::delete_data_for_user($approved);

        $this->assertFalse($DB->record_exists('local_chemillusion_events', ['userid' => $user1->id]));
        $this->assertTrue(
            $DB->record_exists('local_chemillusion_events', ['userid' => $user2->id]),
            'Deleting user1 must not remove user2 event rows'
        );
    }

    public function test_delete_all_users_clears_events(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $system = \context_system::instance();

        $DB->insert_record('local_chemillusion_events', (object) [
            'userid'    => $user->id,
            'courseid'  => 0,
            'eventname' => 'deck_created',
            'surface'   => 'cards',
            'cta'       => null,
            'payload'   => null,
            'created_at' => time(),
        ]);

        provider::delete_data_for_all_users_in_context($system);
        $this->assertEquals(
            0,
            $DB->count_records('local_chemillusion_events'),
            'delete_data_for_all_users_in_context must clear event rows'
        );
    }

    public function test_delete_data_for_users_bulk_removes_event_rows(): void {
        global $DB;
        $this->resetAfterTest();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $system = \context_system::instance();

        foreach ([$user1->id, $user2->id] as $uid) {
            $DB->insert_record('local_chemillusion_events', (object) [
                'userid'    => $uid,
                'courseid'  => 0,
                'eventname' => 'molecule_lookup',
                'surface'   => 'tools',
                'cta'       => null,
                'payload'   => null,
                'created_at' => time(),
            ]);
        }

        $approveduserlist = new \core_privacy\local\request\approved_userlist(
            $system, 'local_chemillusion', [$user1->id]);
        provider::delete_data_for_users($approveduserlist);

        $this->assertFalse(
            $DB->record_exists('local_chemillusion_events', ['userid' => $user1->id]),
            'delete_data_for_users must remove event rows for the listed user'
        );
        $this->assertTrue(
            $DB->record_exists('local_chemillusion_events', ['userid' => $user2->id]),
            'delete_data_for_users must not remove event rows for unlisted users'
        );
    }
}
