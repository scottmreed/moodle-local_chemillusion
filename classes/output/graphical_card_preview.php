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
use moodle_url;


/**
 * View model for a single graphical card preview (student/teacher view).
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class graphical_card_preview implements renderable, templatable {
    /** @var \stdClass */
    protected $card;

    /** @var bool */
    protected $canexport;

    /** @var bool */
    protected $canedit;

    /**
     * Constructor.
     *
     * @param \stdClass $card DB card row.
     * @param bool $canexport Whether export is allowed.
     * @param bool $canedit Whether edit is allowed.
     */
    public function __construct(\stdClass $card, bool $canexport, bool $canedit) {
        $this->card      = $card;
        $this->canexport = $canexport;
        $this->canedit   = $canedit;
    }

    /**
     * Export template context.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $front = json_decode($this->card->frontjson ?? '{}', true) ?? [];
        $back  = json_decode($this->card->backjson ?? '{}', true) ?? [];

        $summary = card_accessibility_summary::generate($this->card->cardtype, $front, $back);
        $summaryvisible = (bool) get_config('local_chemillusion', 'default_accessible_summary_visible');

        return [
            'id'              => $this->card->id,
            'cardtype'        => $this->card->cardtype,
            'name'            => format_string($this->card->name ?? ''),
            'frontjson'       => $this->card->frontjson ?? '{}',
            'backjson'        => $this->card->backjson ?? '{}',
            'renderjson'      => $this->card->renderjson ?? '{}',
            'summary'         => $summary,
            'summaryvisible'  => $summaryvisible,
            'canexport'       => $this->canexport,
            'canedit'         => $this->canedit,
            'editurl' => (new moodle_url(
                '/local/chemillusion/card_edit.php',
                ['id' => $this->card->id]
            ))->out(false),
            'exportsvgurl' => (new moodle_url(
                '/local/chemillusion/card_export.php',
                ['id' => $this->card->id, 'type' => 'svg']
            ))->out(false),
            'is_newman'       => $this->card->cardtype === 'newman_projection',
            'is_orbital'      => $this->card->cardtype === 'orbital_hybridization',
            'is_reaction'     => $this->card->cardtype === 'reaction_coordinate',
            'is_molecule'     => in_array($this->card->cardtype, [
                'molecule_identity', 'functional_group_highlight', 'functional_group_list',
                'smiles_to_structure', 'structure_to_smiles', 'accessibility_card',
            ]),
            'exportenabled'   => (bool) get_config('local_chemillusion', 'enable_card_exports'),
            'pngexportenabled' => (bool) get_config('local_chemillusion', 'enable_png_export'),
        ];
    }
}
