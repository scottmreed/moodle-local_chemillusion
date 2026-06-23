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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/externallib.php');

/**
 * External function: fetch card JSON for client-side preview rendering.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_graphical_card_preview extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cardid' => new external_value(PARAM_INT, 'Card id'),
        ]);
    }

    public static function execute(int $cardid): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), compact('cardid'));

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/chemillusion:view', $context);

        $card = $DB->get_record('local_chemillusion_cards', ['id' => $params['cardid']], '*', MUST_EXIST);

        return [
            'cardid'            => $card->id,
            'cardtype'          => $card->cardtype,
            'name'              => $card->name ?? '',
            'frontjson'         => $card->frontjson ?? '{}',
            'backjson'          => $card->backjson ?? '{}',
            'renderjson'        => $card->renderjson ?? '{}',
            'accessibilityjson' => $card->accessibilityjson ?? '{}',
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'cardid'            => new external_value(PARAM_INT,  'Card id'),
            'cardtype'          => new external_value(PARAM_TEXT, 'Card type'),
            'name'              => new external_value(PARAM_TEXT, 'Card name'),
            'frontjson'         => new external_value(PARAM_RAW,  'Front JSON'),
            'backjson'          => new external_value(PARAM_RAW,  'Back JSON'),
            'renderjson'        => new external_value(PARAM_RAW,  'Render hints JSON'),
            'accessibilityjson' => new external_value(PARAM_RAW,  'Accessibility JSON'),
        ]);
    }
}
