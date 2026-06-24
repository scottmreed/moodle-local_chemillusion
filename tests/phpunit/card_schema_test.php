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
 * Tests for graphical_card_schema and card_type_registry JSON validity.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chemillusion\phpunit;

use local_chemillusion\cards\graphical_card_schema;
use local_chemillusion\cards\card_type_registry;

/**
 * Graphical card schema tests.
 *
 * @coversDefaultClass \local_chemillusion\cards\graphical_card_schema
 */
final class card_schema_test extends \advanced_testcase {
    /**
     * Ensure card_type_registry.json is valid and complete.
     *
     * @covers \local_chemillusion\cards\card_type_registry
     */
    public function test_card_type_registry_json_is_valid(): void {
        $path = __DIR__ . '/../../data/card_type_registry.json';
        $this->assertFileExists($path);
        $data = json_decode(file_get_contents($path), true);
        $this->assertNotNull($data, 'card_type_registry.json must be valid JSON');
        $this->assertIsArray($data);
        $this->assertGreaterThan(0, count($data));
        foreach ($data as $entry) {
            $this->assertArrayHasKey('id', $entry);
            $this->assertArrayHasKey('label', $entry);
            $this->assertArrayHasKey('schema_version', $entry);
        }
    }

    /**
     * Test validate newman projection.
     */
    public function test_validate_newman_projection(): void {
        $valid = [
            'front'            => ['CH3', 'H', 'H'],
            'back'             => ['CH3', 'H', 'H'],
            'rotation_degrees' => 180,
        ];
        $this->assertTrue(graphical_card_schema::validate('newman_projection', $valid));
    }

    /**
     * Test validate newman projection missing back.
     */
    public function test_validate_newman_projection_missing_back(): void {
        $invalid = [
            'front'            => ['CH3', 'H', 'H'],
            'rotation_degrees' => 180,
        ];
        $this->assertFalse(graphical_card_schema::validate('newman_projection', $invalid));
    }

    /**
     * Test validate newman projection wrong front count.
     */
    public function test_validate_newman_projection_wrong_front_count(): void {
        $invalid = [
            'front'            => ['CH3', 'H'],
            'back'             => ['CH3', 'H', 'H'],
            'rotation_degrees' => 180,
        ];
        $this->assertFalse(graphical_card_schema::validate('newman_projection', $invalid));
    }

    /**
     * Test validate reaction coordinate.
     */
    public function test_validate_reaction_coordinate(): void {
        $valid = [
            'template' => 'sn1_profile',
            'points'   => [
                ['id' => 'reactants', 'x' => 0.05, 'y' => 0.55, 'label' => 'R-X'],
                ['id' => 'ts1', 'x' => 0.25, 'y' => 0.12, 'label' => 'TS1'],
                ['id' => 'products', 'x' => 0.95, 'y' => 0.65, 'label' => 'Products'],
            ],
        ];
        $this->assertTrue(graphical_card_schema::validate('reaction_coordinate', $valid));
    }

    /**
     * Test validate molecule identity.
     */
    public function test_validate_molecule_identity(): void {
        $this->assertTrue(graphical_card_schema::validate('molecule_identity', ['smiles' => 'CC']));
        $this->assertFalse(graphical_card_schema::validate('molecule_identity', []));
    }

    /**
     * Test validate unknown type returns false.
     */
    public function test_validate_unknown_type_returns_false(): void {
        $this->assertFalse(graphical_card_schema::validate('made_up_type', ['smiles' => 'C']));
    }

    /**
     * Test errors return array.
     */
    public function test_errors_return_array(): void {
        $errs = graphical_card_schema::errors('newman_projection', []);
        $this->assertIsArray($errs);
        $this->assertNotEmpty($errs);
    }
}
