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

use local_chemillusion\api\pubchem_client;

/**
 * Unit tests for the PubChem client guard logic (no network).
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_chemillusion\api\pubchem_client
 */
final class pubchem_client_test extends \advanced_testcase {

    public function test_external_disabled_blocks_resolve(): void {
        $this->resetAfterTest();
        set_config('disable_external', 1, 'local_chemillusion');
        set_config('enable_pubchem', 1, 'local_chemillusion');
        $result = pubchem_client::resolve('water');
        $this->assertSame('error', $result['status']);
        $this->assertSame('external_disabled', $result['error']);
    }

    public function test_pubchem_disabled_blocks_resolve(): void {
        $this->resetAfterTest();
        set_config('disable_external', 0, 'local_chemillusion');
        set_config('enable_pubchem', 0, 'local_chemillusion');
        $result = pubchem_client::resolve('water');
        $this->assertSame('error', $result['status']);
        $this->assertSame('external_disabled', $result['error']);
    }

    public function test_empty_input_is_invalid(): void {
        $this->resetAfterTest();
        set_config('disable_external', 0, 'local_chemillusion');
        set_config('enable_pubchem', 1, 'local_chemillusion');
        $result = pubchem_client::resolve('    ');
        $this->assertSame('error', $result['status']);
        $this->assertSame('invalidinput', $result['error']);
    }
}
