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

use local_chemillusion\external\save_graphical_card;

/**
 * Tests for save_graphical_card external function.
 *
 * Verifies capability checks, schema validation, and DB persistence.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @runTestsInSeparateProcesses
 *
 * @coversDefaultClass \local_chemillusion\external\save_graphical_card
 */
final class external_save_graphical_card_test extends \advanced_testcase {
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    public function test_create_newman_card_succeeds_as_editingteacher(): void {
        global $DB;
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->role_assign(
            $this->getDataGenerator()->create_role(['archetype' => 'editingteacher']),
            $teacher->id,
            \context_system::instance()->id
        );
        $this->setUser($teacher);

        $frontjson = json_encode([
            'front' => ['CH3', 'H', 'H'],
            'back' => ['CH3', 'H', 'H'],
            'rotation_degrees' => 180,
        ]);

        $result = save_graphical_card::execute(
            0,
            'newman_projection',
            'Butane anti',
            $frontjson,
            '{}',
            '{}',
            '{}',
            0
        );

        $this->assertTrue($result['success']);
        $this->assertGreaterThan(0, $result['cardid']);
        $rec = $DB->get_record('local_chemillusion_cards', ['id' => $result['cardid']]);
        $this->assertNotFalse($rec);
        $this->assertSame('newman_projection', $rec->cardtype);
    }

    public function test_invalid_frontjson_throws(): void {
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->role_assign(
            $this->getDataGenerator()->create_role(['archetype' => 'editingteacher']),
            $teacher->id,
            \context_system::instance()->id
        );
        $this->setUser($teacher);

        $this->expectException(\invalid_parameter_exception::class);
        save_graphical_card::execute(
            0,
            'newman_projection',
            'Bad card',
            json_encode(['front' => ['CH3']]),
            '{}',
            '{}',
            '{}',
            0
        );
    }

    public function test_student_cannot_create_cards(): void {
        $student = $this->getDataGenerator()->create_user();
        $this->setUser($student);
        $this->expectException(\required_capability_exception::class);
        save_graphical_card::execute(
            0,
            'newman_projection',
            'Test card',
            json_encode([
                'front' => ['H', 'H', 'H'],
                'back' => ['H', 'H', 'H'],
                'rotation_degrees' => 60,
            ]),
            '{}',
            '{}',
            '{}',
            0
        );
    }
}
