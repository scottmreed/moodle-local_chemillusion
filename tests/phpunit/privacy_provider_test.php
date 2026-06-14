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

    public function test_contexts_export_and_delete(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $system = \context_system::instance();

        $DB->insert_record('local_chemillusion_links',
            (object) ['userid' => $user->id, 'linked_at' => time(), 'status' => 'linked']);
        $deckid = $DB->insert_record('local_chemillusion_decks', (object) [
            'userid' => $user->id, 'name' => 'D', 'visibility' => 'private',
            'source' => 'manual', 'created_at' => time(), 'updated_at' => time(),
        ]);
        $DB->insert_record('local_chemillusion_cards', (object) [
            'deckid' => $deckid, 'cardtype' => 'molecule_identity', 'prompt' => 'p',
            'answer' => 'a', 'sortorder' => 0, 'created_at' => time(),
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
    }
}
