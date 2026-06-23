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
 * Graphical card export controller.
 *
 * Validates sesskey + capability, then serves SVG or HTML snippet directly.
 * PNG export is handled client-side; this page redirects back if type=png.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$cardid = required_param('id', PARAM_INT);
$type   = optional_param('type', 'svg', PARAM_ALPHA);

require_login();
require_sesskey();

$context = context_system::instance();
require_capability('local/chemillusion:exportcards', $context);

if (!(bool) get_config('local_chemillusion', 'enable_card_exports')) {
    throw new moodle_exception('error_external_disabled', 'local_chemillusion');
}

$card = $DB->get_record('local_chemillusion_cards', ['id' => $cardid], '*', MUST_EXIST);

if ($type === 'png') {
    // PNG export is client-side; redirect back to card view.
    redirect(new moodle_url('/local/chemillusion/card_view.php', ['id' => $cardid]));
}

$front   = json_decode($card->frontjson ?? '{}', true) ?? [];
$back    = json_decode($card->backjson ?? '{}', true) ?? [];
$summary = \local_chemillusion\output\card_accessibility_summary::generate($card->cardtype, $front, $back);

if ($type === 'html_snippet') {
    $safesummary = s($summary);
    $html = '<figure class="local-chemillusion-card-export">'
        . '<div class="local-chemillusion-svg-placeholder" data-cardid="' . (int) $card->id
        . '" data-cardtype="' . s($card->cardtype) . '">'
        . '<!-- SVG rendered client-side by local_chemillusion -->'
        . '</div>'
        . '<figcaption>' . $safesummary . '</figcaption>'
        . '</figure>';
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: attachment; filename="chemillusion-card-' . (int) $card->id . '.html"');
    echo $html;
    exit;
}

// SVG.
$renderjson = json_decode($card->renderjson ?? '{}', true) ?? [];
$svg = $renderjson['svg'] ?? '';
if (!$svg) {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="200">'
        . '<text x="10" y="20" font-size="12" font-family="sans-serif">Card id: ' . (int) $card->id . '</text>'
        . '<text x="10" y="40" font-size="10" font-family="sans-serif">' . s($summary) . '</text>'
        . '</svg>';
}

$name = preg_replace('/[^a-z0-9_-]/i', '_', $card->name ?? 'card');
header('Content-Type: image/svg+xml; charset=UTF-8');
header('Content-Disposition: attachment; filename="chemillusion-' . $name . '.svg"');
echo $svg;
exit;
