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

namespace local_chemillusion\external;

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use context_system;
use local_chemillusion\cards\graphical_card_schema;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/externallib.php');

/**
 * External function: save or update a graphical chemistry card.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_graphical_card extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'id'                => new external_value(PARAM_INT,  'Card id (0 for new)', VALUE_DEFAULT, 0),
            'cardtype'          => new external_value(PARAM_ALPHANUMEXT, 'Card type id'),
            'name'              => new external_value(PARAM_TEXT, 'Card name'),
            'frontjson'         => new external_value(PARAM_RAW,  'Front card data JSON'),
            'backjson'          => new external_value(PARAM_RAW,  'Back card data JSON', VALUE_DEFAULT, '{}'),
            'renderjson'        => new external_value(PARAM_RAW,  'Render hints JSON', VALUE_DEFAULT, '{}'),
            'accessibilityjson' => new external_value(PARAM_RAW,  'Accessibility JSON', VALUE_DEFAULT, '{}'),
            'deckid'            => new external_value(PARAM_INT,  'Deck id (optional)', VALUE_DEFAULT, 0),
        ]);
    }

    public static function execute(
        int    $id,
        string $cardtype,
        string $name,
        string $frontjson,
        string $backjson,
        string $renderjson,
        string $accessibilityjson,
        int    $deckid
    ): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), compact(
            'id', 'cardtype', 'name', 'frontjson', 'backjson', 'renderjson', 'accessibilityjson', 'deckid'
        ));

        $context = context_system::instance();
        self::validate_context($context);

        // Capability check.
        if ($params['id'] > 0) {
            $existing = $DB->get_record('local_chemillusion_cards', ['id' => $params['id']], '*', MUST_EXIST);
            if ($existing->owneruserid == $USER->id) {
                require_capability('local/chemillusion:editowncards', $context);
            } else {
                require_capability('local/chemillusion:editallcards', $context);
            }
        } else {
            require_capability('local/chemillusion:createcards', $context);
        }

        // Validate frontjson structure.
        $frontdata = json_decode($params['frontjson'], true);
        if (!is_array($frontdata) || !graphical_card_schema::validate($params['cardtype'], $frontdata)) {
            throw new \invalid_parameter_exception('Invalid frontjson for card type ' . $params['cardtype']);
        }

        $now = time();

        if ($params['id'] > 0) {
            $record = new \stdClass();
            $record->id               = $params['id'];
            $record->cardtype         = $params['cardtype'];
            $record->name             = $params['name'];
            $record->frontjson        = $params['frontjson'];
            $record->backjson         = $params['backjson'];
            $record->renderjson       = $params['renderjson'];
            $record->accessibilityjson = $params['accessibilityjson'];
            $record->updated_at       = $now;
            if ($params['deckid'] > 0) {
                $record->deckid = $params['deckid'];
            }
            $DB->update_record('local_chemillusion_cards', $record);
            return ['success' => true, 'cardid' => $params['id']];
        }

        $record = new \stdClass();
        $record->owneruserid       = $USER->id;
        $record->cardtype          = $params['cardtype'];
        $record->name              = $params['name'];
        $record->frontjson         = $params['frontjson'];
        $record->backjson          = $params['backjson'];
        $record->renderjson        = $params['renderjson'];
        $record->accessibilityjson = $params['accessibilityjson'];
        $record->source            = 'local';
        $record->sortorder         = 0;
        $record->created_at        = $now;
        $record->updated_at        = $now;
        if ($params['deckid'] > 0) {
            $record->deckid = $params['deckid'];
        }
        $cardid = $DB->insert_record('local_chemillusion_cards', $record);

        return ['success' => true, 'cardid' => $cardid];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the save succeeded'),
            'cardid'  => new external_value(PARAM_INT,  'The saved card id'),
        ]);
    }
}
