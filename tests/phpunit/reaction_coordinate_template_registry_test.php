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
 * Tests for reaction_coordinate_template_registry and JSON data validity.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chemillusion\phpunit;

use local_chemillusion\cards\reaction_coordinate_template_registry;

defined('MOODLE_INTERNAL') || die();

/**
 * Reaction coordinate template registry tests.
 *
 * @coversDefaultClass \local_chemillusion\cards\reaction_coordinate_template_registry
 */
final class reaction_coordinate_template_registry_test extends \advanced_testcase {
    public function test_templates_json_is_valid(): void {
        $path = __DIR__ . '/../../data/reaction_coordinate_templates.json';
        $this->assertFileExists($path);
        $data = json_decode(file_get_contents($path), true);
        $this->assertNotNull($data, 'reaction_coordinate_templates.json must be valid JSON');
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(10, count($data), 'PRD requires 10 MVP templates');
    }

    public function test_examples_json_is_valid(): void {
        $path = __DIR__ . '/../../data/reaction_coordinate_examples.json';
        $this->assertFileExists($path);
        $data = json_decode(file_get_contents($path), true);
        $this->assertNotNull($data, 'reaction_coordinate_examples.json must be valid JSON');
        $this->assertIsArray($data);
    }

    public function test_each_template_has_required_fields(): void {
        $templates = reaction_coordinate_template_registry::get_all();
        foreach ($templates as $t) {
            $this->assertArrayHasKey('id', $t);
            $this->assertArrayHasKey('label', $t);
            $this->assertArrayHasKey('points', $t);
            $this->assertGreaterThanOrEqual(
                2,
                count($t['points']),
                'Each template needs at least reactants and products'
            );
            foreach ($t['points'] as $pt) {
                $this->assertArrayHasKey('id', $pt);
                $this->assertArrayHasKey('x', $pt);
                $this->assertArrayHasKey('y', $pt);
                $this->assertArrayHasKey('label', $pt);
            }
        }
    }

    public function test_get_sn1_profile(): void {
        $t = reaction_coordinate_template_registry::get('sn1_profile');
        $this->assertNotNull($t);
        $this->assertSame('sn1_profile', $t['id']);
        $ids = array_column($t['points'], 'id');
        $this->assertContains('intermediate', $ids);
        $this->assertContains('ts1', $ids);
    }

    public function test_get_sn2_profile_single_ts(): void {
        $t = reaction_coordinate_template_registry::get('sn2_profile');
        $this->assertNotNull($t);
        $ids = array_column($t['points'], 'id');
        $this->assertContains('ts1', $ids);
        $this->assertNotContains('intermediate', $ids);
    }

    public function test_default_card_data_shape(): void {
        $data = reaction_coordinate_template_registry::default_card_data('sn2_profile');
        $this->assertNotNull($data);
        $this->assertSame('reaction_coordinate', $data['type']);
        $this->assertArrayHasKey('points', $data);
        $this->assertIsArray($data['points']);
    }
}
