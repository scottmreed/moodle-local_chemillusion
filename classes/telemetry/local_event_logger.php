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

namespace local_chemillusion\telemetry;

/**
 * Local-only, privacy-light usage counters.
 *
 * Records coarse funnel events (lookups, deck creation, CTA clicks) in the
 * {local_chemillusion_events} table for the teacher/admin dashboard. No grades,
 * prompts, answers, or rosters are stored. Nothing here leaves the Moodle site;
 * external conversion metadata is a separate, consent-gated path.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_event_logger {

    /** @var string Event names. */
    const EVENT_LOOKUP = 'molecule_lookup';
    const EVENT_DECK_CREATED = 'deck_created';
    const EVENT_STUDY_SESSION = 'study_session';
    const EVENT_LINK_CLICK = 'account_link_click';
    const EVENT_DEMO_CLICK = 'teacher_demo_click';
    const EVENT_VISUAL_CARD = 'visual_card_click';

    /**
     * Record a single event.
     *
     * @param string $eventname One of the EVENT_* constants.
     * @param string|null $surface
     * @param string|null $cta
     * @param int|null $courseid
     * @return void
     */
    public static function log($eventname, $surface = null, $cta = null, $courseid = null) {
        global $DB, $USER;

        $minimal = (bool) get_config('local_chemillusion', 'minimal_mode');

        $DB->insert_record('local_chemillusion_events', (object) [
            'userid'     => $minimal ? null : (int) $USER->id,
            'courseid'   => $courseid ? (int) $courseid : null,
            'eventname'  => clean_param($eventname, PARAM_ALPHANUMEXT),
            'surface'    => $surface ? clean_param($surface, PARAM_ALPHANUMEXT) : null,
            'cta'        => $cta ? clean_param($cta, PARAM_ALPHANUMEXT) : null,
            'payload'    => null,
            'created_at' => time(),
        ]);
    }

    /**
     * Count events by name, optionally scoped to a course.
     *
     * @param string $eventname
     * @param int|null $courseid
     * @return int
     */
    public static function count($eventname, $courseid = null) {
        global $DB;
        $params = ['eventname' => $eventname];
        if ($courseid !== null) {
            $params['courseid'] = (int) $courseid;
        }
        return (int) $DB->count_records('local_chemillusion_events', $params);
    }

    /**
     * Return the dashboard metric set.
     *
     * @param int|null $courseid
     * @return array
     */
    public static function dashboard_metrics($courseid = null) {
        return [
            'lookups'     => self::count(self::EVENT_LOOKUP, $courseid),
            'decks'       => self::count(self::EVENT_DECK_CREATED, $courseid),
            'sessions'    => self::count(self::EVENT_STUDY_SESSION, $courseid),
            'link_clicks' => self::count(self::EVENT_LINK_CLICK, $courseid),
            'demo_clicks' => self::count(self::EVENT_DEMO_CLICK, $courseid),
        ];
    }
}
