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

namespace local_chemillusion\auth;

use local_chemillusion\api\chemillusion_client;
use local_chemillusion\security\signed_state;

/**
 * Manages the minimal Moodle to ChemIllusion account mapping and the signed
 * link handoff. Stores only an opaque account id, an optional email hash, and
 * timestamps. Never stores grades, rosters, prompts, or full profiles.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class account_linker {

    /**
     * Whether linking is enabled site-wide.
     *
     * @return bool
     */
    public static function is_enabled() {
        return chemillusion_client::linking_enabled();
    }

    /**
     * Get the current link record for a user, or null.
     *
     * @param int $userid
     * @return \stdClass|null
     */
    public static function get_link($userid) {
        global $DB;
        $record = $DB->get_record('local_chemillusion_links', ['userid' => $userid]);
        return $record ?: null;
    }

    /**
     * Begin a link: returns the signed ChemIllusion URL to redirect to.
     *
     * @param int $userid
     * @param string $role
     * @param string $surface
     * @return string
     */
    public static function start_link($userid, $role, $surface) {
        global $DB;

        $now = time();
        $existing = self::get_link($userid);
        if ($existing) {
            $existing->status = 'pending';
            $existing->last_launch_at = $now;
            $DB->update_record('local_chemillusion_links', $existing);
        } else {
            $DB->insert_record('local_chemillusion_links', (object) [
                'userid'                => $userid,
                'chemillusion_user_id'  => null,
                'chemillusion_email_hash' => null,
                'linked_at'             => 0,
                'last_launch_at'        => $now,
                'status'                => 'pending',
            ]);
        }

        $meta = chemillusion_client::build_source_metadata($role, $surface, 'account_link');
        $meta['moodle_userid'] = (int) $userid;
        return chemillusion_client::link_start_url($meta);
    }

    /**
     * Complete a link from a verified return token.
     *
     * @param int $userid
     * @param string $returntoken Signed token returned by ChemIllusion.
     * @return bool Success.
     */
    public static function complete_link($userid, $returntoken) {
        global $DB;

        $payload = signed_state::verify($returntoken);
        if ($payload === null) {
            return false;
        }
        // The token must be bound to this Moodle user.
        if (!isset($payload['moodle_userid']) || (int) $payload['moodle_userid'] !== (int) $userid) {
            return false;
        }

        $chemuserid = isset($payload['chemillusion_user_id'])
            ? clean_param($payload['chemillusion_user_id'], PARAM_ALPHANUMEXT) : null;
        if ($chemuserid === null || $chemuserid === '') {
            return false;
        }
        $emailhash = isset($payload['email_hash'])
            ? clean_param($payload['email_hash'], PARAM_ALPHANUMEXT) : null;

        $now = time();
        $record = self::get_link($userid);
        if (!$record) {
            $record = (object) ['userid' => $userid];
            $record->id = $DB->insert_record('local_chemillusion_links', (object) [
                'userid'    => $userid,
                'linked_at' => $now,
                'status'    => 'linked',
            ]);
        }
        $record->chemillusion_user_id = $chemuserid;
        $record->chemillusion_email_hash = $emailhash;
        $record->linked_at = $now;
        $record->last_launch_at = $now;
        $record->status = 'linked';
        $DB->update_record('local_chemillusion_links', $record);

        return true;
    }

    /**
     * Whether the user already has a completed link.
     *
     * @param int $userid
     * @return bool
     */
    public static function is_linked($userid) {
        $record = self::get_link($userid);
        return $record !== null && $record->status === 'linked';
    }
}
