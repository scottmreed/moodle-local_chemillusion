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

namespace local_chemillusion\output;

use renderer_base;
use templatable;
use renderable;
use local_chemillusion\api\chemillusion_client;


/**
 * Molecule result card view model.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class molecule_card implements renderable, templatable {
    /** @var array Normalised molecule payload. */
    protected $payload;

    /** @var string Viewer role for funnel metadata. */
    protected $role;

    /**
     * Constructor.
     *
     * @param array $payload
     * @param string $role
     */
    public function __construct(array $payload, $role = 'student') {
        $this->payload = $payload;
        $this->role = $role;
    }

    /**
     * Export for the molecule_result_card template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        $p = $this->payload;
        $meta = chemillusion_client::build_source_metadata($this->role, 'molecule_lookup', 'open_in_generator');
        $context = [
            'name'             => $p['name'] ?? '',
            'cid'              => $p['cid'] ?? '',
            'formula'          => $p['formula'] ?? '',
            'canonical_smiles' => $p['canonical_smiles'] ?? '',
            'isomeric_smiles'  => $p['isomeric_smiles'] ?? '',
            'inchikey'         => $p['inchikey'] ?? '',
        ];

        $visualenabled = (bool) get_config('local_chemillusion', 'enable_visual_preview');

        return [
            'name'             => $p['name'] ?? '',
            'cid'              => $p['cid'] ?? '',
            'formula'          => $p['formula'] ?? '',
            'mw'               => $p['mw'] ?? '',
            'canonical_smiles' => $p['canonical_smiles'] ?? '',
            'isomeric_smiles'  => $p['isomeric_smiles'] ?? '',
            'inchikey'         => $p['inchikey'] ?? '',
            'pubchem_url'      => $p['pubchem_url'] ?? '',
            'has_pubchem'      => !empty($p['pubchem_url']),
            'has_smiles'       => !empty($p['canonical_smiles']),
            'rdkit_enabled'    => (bool) get_config('local_chemillusion', 'enable_rdkit'),
            'open_url'         => chemillusion_client::continue_url($meta, $context),
            'visual_enabled'   => $visualenabled,
            'visual_url'       => $visualenabled ? chemillusion_client::visual_card_url($meta, $context) : '',
            'visual_blurb'     => get_string('cta_visual_card_blurb', 'local_chemillusion'),
        ];
    }
}
