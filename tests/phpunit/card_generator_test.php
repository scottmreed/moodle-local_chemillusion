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

use local_chemillusion\cards\card_generator;

/**
 * Unit tests for card generation and sanitisation.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_chemillusion\cards\card_generator
 */
final class card_generator_test extends \advanced_testcase {
    public function test_from_molecule(): void {
        $card = card_generator::from_molecule([
            'name' => 'Aspirin', 'formula' => 'C9H8O4', 'mw' => '180.16',
            'cid' => 2244, 'canonical_smiles' => 'CC(=O)Oc1ccccc1C(=O)O',
            'inchikey' => 'BSYNRYMUTXBXSQ-UHFFFAOYSA-N',
        ]);
        $this->assertSame('molecule_identity', $card['cardtype']);
        $this->assertSame('Aspirin', $card['prompt']);
        $this->assertStringContainsString('C9H8O4', $card['answer']);
        $this->assertStringContainsString('2244', $card['answer']);
    }

    public function test_from_reagent(): void {
        $card = card_generator::from_reagent('PCC', [
            'name' => 'pyridinium chlorochromate', 'role' => 'oxidant',
            'common_use' => 'oxidises alcohols',
        ]);
        $this->assertSame('reagent', $card['cardtype']);
        $this->assertSame('PCC', $card['prompt']);
        $this->assertStringContainsString('oxidant', $card['answer']);
    }

    public function test_from_functional_group(): void {
        $card = card_generator::from_functional_group('ester', [
            'label' => 'Ester', 'smarts' => '[CX3](=O)[OX2H0][#6]',
            'student_summary' => 'Carbonyl with OR', 'common_mistake' => 'OR not OH',
        ]);
        $this->assertSame('functional_group', $card['cardtype']);
        $this->assertSame('ester', $card['molecule_payload']['group']);
    }

    public function test_sanitize_strips_nonscalar_payload(): void {
        $card = card_generator::sanitize([
            'cardtype' => 'custom', 'prompt' => 'p', 'answer' => 'a',
            'molecule_payload' => ['cid' => 1, 'bad' => ['nested' => 1]],
        ]);
        $this->assertArrayHasKey('cid', $card['molecule_payload']);
        $this->assertArrayNotHasKey('bad', $card['molecule_payload']);
    }
}
