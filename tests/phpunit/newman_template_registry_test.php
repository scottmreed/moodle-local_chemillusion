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

/**
 * Tests for newman_template_registry and data/newman_templates.json validity.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_chemillusion\cards\newman_template_registry
 */

namespace local_chemillusion\phpunit;

use local_chemillusion\cards\newman_template_registry;

defined('MOODLE_INTERNAL') || die();

/**
 * Newman template registry tests.
 */
final class newman_template_registry_test extends \advanced_testcase {
    public function test_json_is_valid(): void {
        $path = __DIR__ . '/../../data/newman_templates.json';
        $this->assertFileExists($path);
        $data = json_decode(file_get_contents($path), true);
        $this->assertNotNull($data, 'newman_templates.json must be valid JSON');
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(8, count($data), 'PRD requires 8 MVP templates');
    }

    public function test_each_template_has_required_fields(): void {
        $templates = newman_template_registry::get_all();
        foreach ($templates as $t) {
            $this->assertArrayHasKey('id', $t);
            $this->assertArrayHasKey('label', $t);
            $this->assertArrayHasKey('front', $t);
            $this->assertArrayHasKey('back', $t);
            $this->assertArrayHasKey('rotation_degrees', $t);
            $this->assertCount(3, $t['front'], 'Front must have exactly 3 substituents');
            $this->assertCount(3, $t['back'], 'Back must have exactly 3 substituents');
        }
    }

    public function test_get_known_template(): void {
        $t = newman_template_registry::get('butane_anti');
        $this->assertNotNull($t);
        $this->assertSame('butane_anti', $t['id']);
        $this->assertSame(180, $t['rotation_degrees']);
    }

    public function test_get_unknown_template_returns_null(): void {
        $this->assertNull(newman_template_registry::get('nonexistent_template'));
    }

    public function test_default_card_data_shape(): void {
        $data = newman_template_registry::default_card_data('ethane_staggered');
        $this->assertNotNull($data);
        $this->assertSame('newman_projection', $data['type']);
        $this->assertArrayHasKey('front', $data);
        $this->assertArrayHasKey('back', $data);
        $this->assertArrayHasKey('rotation_degrees', $data);
    }
}
