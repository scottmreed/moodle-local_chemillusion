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

namespace local_chemillusion\cache;

/**
 * Simple server-side cache for public lookup payloads.
 *
 * Backed by the {local_chemillusion_cache} table so cached PubChem responses
 * persist across requests without per-student query history.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class molecule_cache {

    /** @var string Default cache bucket name. */
    const DEFAULT_NAME = 'pubchem';

    /**
     * Fetch a decoded payload by key, or null when missing/expired.
     *
     * @param string $key
     * @return array|null
     */
    public static function get($key) {
        global $DB;
        $record = $DB->get_record('local_chemillusion_cache', ['cachekey' => $key]);
        if (!$record) {
            return null;
        }
        if ((int) $record->expires_at > 0 && (int) $record->expires_at < time()) {
            $DB->delete_records('local_chemillusion_cache', ['id' => $record->id]);
            return null;
        }
        $decoded = json_decode($record->payload, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Store a payload under a key with a TTL in seconds.
     *
     * @param string $key
     * @param array $payload
     * @param int $ttl Seconds until expiry; 0 means use default week.
     * @param string $name Cache bucket name.
     * @return void
     */
    public static function set($key, array $payload, $ttl = 0, $name = self::DEFAULT_NAME) {
        global $DB;
        $now = time();
        $ttl = (int) $ttl > 0 ? (int) $ttl : 604800;

        $existing = $DB->get_record('local_chemillusion_cache', ['cachekey' => $key]);
        $data = (object) [
            'cachekey'   => $key,
            'cachename'  => $name,
            'payload'    => json_encode($payload),
            'expires_at' => $now + $ttl,
            'created_at' => $now,
        ];
        if ($existing) {
            $data->id = $existing->id;
            $DB->update_record('local_chemillusion_cache', $data);
        } else {
            $DB->insert_record('local_chemillusion_cache', $data);
        }
    }

    /**
     * Delete all expired rows.
     *
     * @return int Number of rows removed.
     */
    public static function purge_expired() {
        global $DB;
        $now = time();
        $count = $DB->count_records_select('local_chemillusion_cache', 'expires_at > 0 AND expires_at < :now', ['now' => $now]);
        $DB->delete_records_select('local_chemillusion_cache', 'expires_at > 0 AND expires_at < :now', ['now' => $now]);
        return (int) $count;
    }

    /**
     * Build a namespaced cache key for an identifier lookup.
     *
     * @param string $type
     * @param string $value
     * @return string
     */
    public static function make_key($type, $value) {
        return 'lookup:' . $type . ':' . sha1(\core_text::strtolower($value));
    }
}
