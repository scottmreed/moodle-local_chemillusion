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

use local_chemillusion\cache\molecule_cache;

/**
 * Unit tests for the server-side molecule cache.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @coversDefaultClass \local_chemillusion\cache\molecule_cache
 */
final class molecule_cache_test extends \advanced_testcase {
    public function test_set_and_get(): void {
        $this->resetAfterTest();
        molecule_cache::set('k1', ['a' => 1], 100);
        $this->assertSame(['a' => 1], molecule_cache::get('k1'));
    }

    public function test_expired_returns_null_and_removes_row(): void {
        global $DB;
        $this->resetAfterTest();
        molecule_cache::set('k2', ['a' => 1], 100);
        $DB->set_field('local_chemillusion_cache', 'expires_at', time() - 10, ['cachekey' => 'k2']);
        $this->assertNull(molecule_cache::get('k2'));
        $this->assertFalse($DB->record_exists('local_chemillusion_cache', ['cachekey' => 'k2']));
    }

    public function test_purge_expired(): void {
        global $DB;
        $this->resetAfterTest();
        molecule_cache::set('valid', ['v' => 1], 1000);
        molecule_cache::set('old', ['v' => 2], 1000);
        $DB->set_field('local_chemillusion_cache', 'expires_at', time() - 5, ['cachekey' => 'old']);
        $count = molecule_cache::purge_expired();
        $this->assertSame(1, $count);
        $this->assertTrue($DB->record_exists('local_chemillusion_cache', ['cachekey' => 'valid']));
        $this->assertFalse($DB->record_exists('local_chemillusion_cache', ['cachekey' => 'old']));
    }

    public function test_make_key_is_case_insensitive(): void {
        $this->assertSame(
            molecule_cache::make_key('name', 'Water'),
            molecule_cache::make_key('name', 'water')
        );
    }
}
