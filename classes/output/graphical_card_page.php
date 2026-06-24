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
use local_chemillusion\cards\card_type_registry;

defined('MOODLE_INTERNAL') || die();

/**
 * View model for the graphical cards browse/create page.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class graphical_card_page implements renderable, templatable {
    /** @var array Existing card stubs for the picker list. */
    protected $cards;

    /** @var bool Whether viewer can create cards. */
    protected $cancreate;

    /**
     * Constructor.
     *
     * @param array $cards Array of card rows (id, name, cardtype, created_at).
     * @param bool $cancreate Whether the viewer can create cards.
     */
    public function __construct(array $cards, bool $cancreate) {
        $this->cards     = $cards;
        $this->cancreate = $cancreate;
    }

    /**
     * Export template context.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $types = card_type_registry::get_enabled_types();
        $typeoptions = [];
        foreach ($types as $tid) {
            $type = card_type_registry::get($tid);
            $typeoptions[] = [
                'id'    => $tid,
                'label' => get_string('cardtype_' . $tid, 'local_chemillusion'),
            ];
        }

        $cardrows = [];
        foreach ($this->cards as $card) {
            $cardrows[] = [
                'id'        => $card->id,
                'name'      => format_string($card->name ?? ''),
                'cardtype'  => get_string('cardtype_' . $card->cardtype, 'local_chemillusion'),
                'viewurl'   => (new moodle_url('/local/chemillusion/card_view.php', ['id' => $card->id]))->out(false),
                'editurl'   => (new moodle_url('/local/chemillusion/card_edit.php', ['id' => $card->id]))->out(false),
            ];
        }

        return [
            'heading'      => get_string('graphical_cards_heading', 'local_chemillusion'),
            'intro'        => get_string('graphical_cards_intro', 'local_chemillusion'),
            'cancreate'    => $this->cancreate,
            'createurl'    => (new moodle_url('/local/chemillusion/card_edit.php'))->out(false),
            'typeoptions'  => $typeoptions,
            'cards'        => $cardrows,
            'nocards'      => empty($cardrows),
            'dashboardurl' => (new moodle_url('/local/chemillusion/index.php'))->out(false),
        ];
    }
}
