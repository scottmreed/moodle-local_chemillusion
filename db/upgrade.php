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
 * Upgrade steps for local_chemillusion.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute local_chemillusion upgrade steps.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool
 */
function xmldb_local_chemillusion_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026062201) {
        $table = new xmldb_table('local_chemillusion_cards');

        $fields = [
            new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'id'),
            new xmldb_field('owneruserid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'courseid'),
            new xmldb_field('name', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'owneruserid'),
            new xmldb_field('frontjson', XMLDB_TYPE_TEXT, null, null, null, null, null, 'name'),
            new xmldb_field('backjson', XMLDB_TYPE_TEXT, null, null, null, null, null, 'frontjson'),
            new xmldb_field('renderjson', XMLDB_TYPE_TEXT, null, null, null, null, null, 'backjson'),
            new xmldb_field('accessibilityjson', XMLDB_TYPE_TEXT, null, null, null, null, null, 'renderjson'),
            new xmldb_field('source', XMLDB_TYPE_CHAR, '64', null, null, null, 'local', 'accessibilityjson'),
            new xmldb_field('updated_at', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'created_at'),
        ];

        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        // Add local_chemillusion_card_exports table.
        $exports = new xmldb_table('local_chemillusion_card_exports');
        $exports->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $exports->add_field('cardid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $exports->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $exports->add_field('exporttype', XMLDB_TYPE_CHAR, '16', null, XMLDB_NOTNULL);
        $exports->add_field('contenthash', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL);
        $exports->add_field('filearea', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL);
        $exports->add_field('itemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $exports->add_field('created_at', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $exports->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $exports->add_key('cardid', XMLDB_KEY_FOREIGN, ['cardid'], 'local_chemillusion_cards', ['id']);
        $exports->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $exports->add_index('exporttype', XMLDB_INDEX_NOTUNIQUE, ['exporttype']);

        if (!$dbman->table_exists($exports)) {
            $dbman->create_table($exports);
        }

        upgrade_plugin_savepoint(true, 2026062201, 'local', 'chemillusion');
    }

    return true;
}
