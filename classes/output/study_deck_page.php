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
use local_chemillusion\cards\deck_repository;

defined('MOODLE_INTERNAL') || die();

/**
 * Study deck list view model.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class study_deck_page implements renderable, templatable {

    /** @var int Owner user id. */
    protected $userid;

    /** @var int|null Course id. */
    protected $courseid;

    /** @var bool Whether viewer can create decks. */
    protected $cancreate;

    /**
     * Constructor.
     *
     * @param int $userid
     * @param int|null $courseid
     * @param bool $cancreate
     */
    public function __construct($userid, $courseid, $cancreate) {
        $this->userid = (int) $userid;
        $this->courseid = $courseid;
        $this->cancreate = (bool) $cancreate;
    }

    /**
     * Export for the study_deck template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        global $DB;
        $decks = [];
        foreach (deck_repository::list_decks($this->userid, $this->courseid) as $deck) {
            $decks[] = [
                'id'        => $deck->id,
                'name'      => format_string($deck->name),
                'cardcount' => (int) $DB->count_records('local_chemillusion_cards', ['deckid' => $deck->id]),
                'viewurl'   => (new moodle_url('/local/chemillusion/cards.php', ['deckid' => $deck->id]))->out(false),
            ];
        }

        return [
            'heading'   => get_string('cards_heading', 'local_chemillusion'),
            'decks'     => $decks,
            'hasdecks'  => !empty($decks),
            'emptytext' => get_string('deck_empty', 'local_chemillusion'),
            'cancreate' => $this->cancreate,
            'courseid'  => $this->courseid ?: 0,
        ];
    }
}
