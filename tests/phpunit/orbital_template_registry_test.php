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
 * Tests for orbital_template_registry and data/orbital_templates.json validity.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chemillusion\phpunit;

use local_chemillusion\cards\orbital_template_registry;


/**
 * Orbital template registry tests.
 *
 * @coversDefaultClass \local_chemillusion\cards\orbital_template_registry
 */
final class orbital_template_registry_test extends \advanced_testcase {
    public function test_json_is_valid(): void {
        $path = __DIR__ . '/../../data/orbital_templates.json';
        $this->assertFileExists($path);
        $data = json_decode(file_get_contents($path), true);
        $this->assertNotNull($data, 'orbital_templates.json must be valid JSON');
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(9, count($data), 'PRD requires 9 curated cases');
    }

    /**
     * Test each template has required fields.
     */
    public function test_each_template_has_required_fields(): void {
        $templates = orbital_template_registry::get_all();
        foreach ($templates as $t) {
            $this->assertArrayHasKey('id', $t);
            $this->assertArrayHasKey('label', $t);
            $this->assertArrayHasKey('confidence', $t);
            $this->assertArrayHasKey('description', $t);
            $this->assertArrayHasKey('hybridization', $t);
        }
    }

    /**
     * Test get known template.
     */
    public function test_get_known_template(): void {
        $t = orbital_template_registry::get('ethene_pi_bond');
        $this->assertNotNull($t);
        $this->assertSame('ethene_pi_bond', $t['id']);
        $this->assertSame('curated', $t['confidence']);
    }

    /**
     * Test get unknown returns null.
     */
    public function test_get_unknown_returns_null(): void {
        $this->assertNull(orbital_template_registry::get('not_a_real_template'));
    }

    /**
     * Test find by smiles ethene.
     */
    public function test_find_by_smiles_ethene(): void {
        $t = orbital_template_registry::find_by_smiles('C=C');
        $this->assertNotNull($t);
        $this->assertSame('ethene_pi_bond', $t['id']);
    }

    /**
     * Test find by smiles unknown returns null.
     */
    public function test_find_by_smiles_unknown_returns_null(): void {
        $this->assertNull(orbital_template_registry::find_by_smiles('CCCC'));
    }

    /**
     * Test asset path for known template with existing file.
     */
    public function test_asset_path_for_known_template_with_existing_file(): void {
        // Only checks path validity for shipped SVG assets.
        $t = orbital_template_registry::get('ethene_pi_bond');
        if ($t && !empty($t['asset'])) {
            $full = __DIR__ . '/../../' . $t['asset'];
            if (file_exists($full)) {
                $this->assertStringEndsWith('.svg', $full);
            } else {
                $this->markTestSkipped('Asset file not yet present; acceptable during development.');
            }
        } else {
            $this->markTestSkipped('No asset defined for ethene_pi_bond.');
        }
    }
}
