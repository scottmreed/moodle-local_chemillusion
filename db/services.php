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
 * External (web service / AJAX) function definitions for local_chemillusion.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_chemillusion_lookup_molecule' => [
        'classname'    => 'local_chemillusion\\external\\lookup_molecule',
        'methodname'   => 'execute',
        'description'  => 'Resolve a molecule by name/SMILES/InChI/InChIKey via PubChem with server-side cache.',
        'type'         => 'read',
        'ajax'         => true,
        'capabilities' => 'local/chemillusion:view',
    ],
    'local_chemillusion_save_deck' => [
        'classname'    => 'local_chemillusion\\external\\save_deck',
        'methodname'   => 'execute',
        'description'  => 'Create or update a local study deck and its cards.',
        'type'         => 'write',
        'ajax'         => true,
        'capabilities' => 'local/chemillusion:managedecks',
    ],
];
