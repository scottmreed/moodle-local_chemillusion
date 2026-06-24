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

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use local_chemillusion\output\card_accessibility_summary;


/**
 * AJAX endpoint: export a graphical card as SVG or HTML snippet.
 *
 * PNG export is handled entirely client-side; this endpoint returns a flag.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class export_graphical_card extends external_api {
    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'cardid'     => new external_value(PARAM_INT, 'Card id'),
            'exporttype' => new external_value(PARAM_ALPHA, 'Export type: svg | html_snippet | png'),
        ]);
    }

    /**
     * Export the card.
     *
     * @param int $cardid
     * @param string $exporttype
     * @return array
     */
    public static function execute($cardid, $exporttype) {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'cardid' => $cardid,
            'exporttype' => $exporttype,
        ]);

        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/chemillusion:exportcards', $context);

        if (!(bool) get_config('local_chemillusion', 'enable_card_exports')) {
            throw new \moodle_exception('error_external_disabled', 'local_chemillusion');
        }

        $card = $DB->get_record('local_chemillusion_cards', ['id' => $params['cardid']], '*', MUST_EXIST);

        if ($params['exporttype'] === 'png') {
            return ['content' => '', 'mimetype' => 'image/png', 'client_side_png' => true];
        }

        $front = json_decode($card->frontjson ?? '{}', true) ?? [];
        $back = json_decode($card->backjson ?? '{}', true) ?? [];
        $summary = card_accessibility_summary::generate($card->cardtype, $front, $back);

        if ($params['exporttype'] === 'html_snippet') {
            $safesummary = s($summary);
            $content = '<figure class="local-chemillusion-card-export">'
                . '<div class="local-chemillusion-svg-placeholder" data-cardid="' . (int) $card->id
                . '" data-cardtype="' . s($card->cardtype) . '">'
                . '<!-- SVG rendered by local_chemillusion -->'
                . '</div>'
                . '<figcaption>' . $safesummary . '</figcaption>'
                . '</figure>';
            return ['content' => $content, 'mimetype' => 'text/html', 'client_side_png' => false];
        }

        $renderjson = json_decode($card->renderjson ?? '{}', true) ?? [];
        $svg = $renderjson['svg'] ?? '';
        if (!$svg) {
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="200">'
                . '<text x="10" y="20" font-size="12">Render client-side using card id ' . (int) $card->id . '</text>'
                . '<text x="10" y="40" font-size="10">' . s($summary) . '</text>'
                . '</svg>';
        }
        return ['content' => $svg, 'mimetype' => 'image/svg+xml', 'client_side_png' => false];
    }

    /**
     * Return description.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'content'         => new external_value(PARAM_RAW, 'SVG string or HTML snippet'),
            'mimetype'        => new external_value(PARAM_TEXT, 'MIME type'),
            'client_side_png' => new external_value(PARAM_BOOL, 'PNG export must happen in browser'),
        ]);
    }
}
