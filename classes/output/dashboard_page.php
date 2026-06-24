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
use local_chemillusion\api\chemillusion_client;
use local_chemillusion\cards\diagram_tool_catalog;
use local_chemillusion\telemetry\local_event_logger;


/**
 * Landing/dashboard view model.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard_page implements renderable, templatable {
    /** @var bool Whether the viewer can manage decks (teacher/manager). */
    protected $isteacher;

    /** @var int|null Course context id. */
    protected $courseid;

    /**
     * Constructor.
     *
     * @param bool $isteacher
     * @param int|null $courseid
     */
    public function __construct($isteacher, $courseid = null) {
        $this->isteacher = (bool) $isteacher;
        $this->courseid = $courseid;
    }

    /**
     * Export for the dashboard template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        $graphicalenabled = (bool) get_config('local_chemillusion', 'enable_graphical_cards');
        $cancreate = has_capability('local/chemillusion:createcards', \context_system::instance());
        $diagramtools = [];
        if ($graphicalenabled && $cancreate) {
            foreach (diagram_tool_catalog::get_all() as $tool) {
                $tool['title'] = get_string($tool['titlekey'], 'local_chemillusion');
                $tool['description'] = get_string($tool['descriptionkey'], 'local_chemillusion');
                $tool['imagealt'] = get_string($tool['imagealtkey'], 'local_chemillusion');
                $tool['url'] = (new moodle_url(
                    '/local/chemillusion/card_edit.php',
                    $tool['editorparams']
                ))->out(false);
                $tool['is_molecule'] = $tool['illustration'] === 'molecule';
                $tool['is_newman'] = $tool['illustration'] === 'newman';
                $tool['is_reaction'] = $tool['illustration'] === 'reaction';
                $tool['is_orbital'] = $tool['illustration'] === 'orbital';
                if ($tool['is_newman'] || $tool['is_reaction']) {
                    $tool['imageurl'] = $output->image_url(
                        'tools/' . ($tool['is_newman'] ? 'newman-projection' : 'reaction-coordinate'),
                        'local_chemillusion'
                    )->out(false);
                }
                $diagramtools[] = $tool;
            }
        }
        $data = [
            'heading'           => get_string('dashboard_heading', 'local_chemillusion'),
            'intro'             => get_string('dashboard_intro', 'local_chemillusion'),
            'toolsurl'          => (new moodle_url('/local/chemillusion/tools.php'))->out(false),
            'cardsurl'          => (new moodle_url('/local/chemillusion/cards.php'))->out(false),
            'graphicalurl'      => (new moodle_url('/local/chemillusion/graphical.php'))->out(false),
            'graphical_enabled' => $graphicalenabled,
            'diagram_tools'     => $diagramtools,
            'has_diagram_tools' => !empty($diagramtools),
            'privacyurl'        => (new moodle_url('/local/chemillusion/privacy.php'))->out(false),
            'linking_enabled'   => chemillusion_client::linking_enabled(),
            'linkurl'           => (new moodle_url('/local/chemillusion/link.php'))->out(false),
            'isteacher'         => $this->isteacher,
        ];

        if ($this->isteacher) {
            $m = local_event_logger::dashboard_metrics($this->courseid);
            $data['teacher_blurb'] = get_string('teacher_dashboard_blurb', 'local_chemillusion');
            $data['demo_url'] = chemillusion_client::teacher_demo_url();
            $data['metrics'] = [
                ['label' => get_string('metric_lookups', 'local_chemillusion'), 'value' => $m['lookups']],
                ['label' => get_string('metric_decks', 'local_chemillusion'), 'value' => $m['decks']],
                ['label' => get_string('metric_sessions', 'local_chemillusion'), 'value' => $m['sessions']],
                ['label' => get_string('metric_link_clicks', 'local_chemillusion'), 'value' => $m['link_clicks']],
                ['label' => get_string('metric_demo_clicks', 'local_chemillusion'), 'value' => $m['demo_clicks']],
            ];
        }

        return $data;
    }
}
