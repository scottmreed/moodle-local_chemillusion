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

use local_chemillusion\auth\account_linker;
use local_chemillusion\security\signed_state;

/**
 * Unit tests for account linking and signed-state handoff.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_chemillusion\auth\account_linker
 */
final class account_linker_test extends \advanced_testcase {
    public function test_start_link_creates_pending_record(): void {
        global $DB;
        $this->resetAfterTest();
        set_config('enable_account_linking', 1, 'local_chemillusion');
        set_config('chemillusion_base_url', 'https://chemillusion.com', 'local_chemillusion');
        $user = $this->getDataGenerator()->create_user();

        $url = account_linker::start_link($user->id, 'student', 'molecule_lookup');

        $this->assertStringContainsString('chemillusion.com', $url);
        $record = $DB->get_record('local_chemillusion_links', ['userid' => $user->id]);
        $this->assertNotEmpty($record);
        $this->assertSame('pending', $record->status);
    }

    public function test_complete_link_links_account(): void {
        $this->resetAfterTest();
        set_config('enable_account_linking', 1, 'local_chemillusion');
        $user = $this->getDataGenerator()->create_user();

        $token = signed_state::sign([
            'moodle_userid' => $user->id,
            'chemillusion_user_id' => 'abc123',
            'email_hash' => 'deadbeef',
        ]);

        $this->assertTrue(account_linker::complete_link($user->id, $token));
        $this->assertTrue(account_linker::is_linked($user->id));
        $record = account_linker::get_link($user->id);
        $this->assertSame('abc123', $record->chemillusion_user_id);
        $this->assertSame('linked', $record->status);
    }

    public function test_complete_link_rejects_mismatched_user(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $token = signed_state::sign(['moodle_userid' => $other->id, 'chemillusion_user_id' => 'x']);
        $this->assertFalse(account_linker::complete_link($user->id, $token));
    }

    public function test_complete_link_requires_external_account_id(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $token = signed_state::sign(['moodle_userid' => $user->id]);
        $this->assertFalse(account_linker::complete_link($user->id, $token));
        $this->assertFalse(account_linker::is_linked($user->id));
    }

    public function test_tampered_token_is_rejected(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $token = signed_state::sign(['moodle_userid' => $user->id]) . 'tampered';
        $this->assertFalse(account_linker::complete_link($user->id, $token));
    }
}
