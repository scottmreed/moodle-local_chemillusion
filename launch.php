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
 * Signed ChemIllusion launch/redirect helper for funnel CTAs.
 *
 * Builds a signed, time-limited state token with PII-free source metadata and
 * optional public molecule context, logs a local event, and redirects the user
 * to the appropriate ChemIllusion entry point.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$cta = optional_param('cta', 'continue', PARAM_ALPHANUMEXT);
$surface = optional_param('surface', 'study_card', PARAM_ALPHANUMEXT);

require_login();
require_sesskey();
$context = context_system::instance();
require_capability('local/chemillusion:view', $context);

$client = '\\local_chemillusion\\api\\chemillusion_client';
$logger = '\\local_chemillusion\\telemetry\\local_event_logger';

$role = has_capability('local/chemillusion:managedecks', $context) ? 'teacher' : 'student';
$meta = \local_chemillusion\api\chemillusion_client::build_source_metadata($role, $surface, $cta);

$ctx = [
    'name'             => optional_param('name', '', PARAM_TEXT),
    'cid'              => optional_param('cid', '', PARAM_ALPHANUM),
    'formula'          => optional_param('formula', '', PARAM_TEXT),
    'canonical_smiles' => optional_param('smiles', '', PARAM_TEXT),
    'inchikey'         => optional_param('inchikey', '', PARAM_TEXT),
    'group'            => optional_param('group', '', PARAM_ALPHANUMEXT),
];

switch ($cta) {
    case 'visual_card':
        \local_chemillusion\telemetry\local_event_logger::log(
            \local_chemillusion\telemetry\local_event_logger::EVENT_VISUAL_CARD, $surface, $cta);
        $target = \local_chemillusion\api\chemillusion_client::visual_card_url($meta, $ctx);
        break;
    case 'teacher_demo':
        \local_chemillusion\telemetry\local_event_logger::log(
            \local_chemillusion\telemetry\local_event_logger::EVENT_DEMO_CLICK, $surface, $cta);
        $target = \local_chemillusion\api\chemillusion_client::teacher_demo_url();
        break;
    default:
        $target = \local_chemillusion\api\chemillusion_client::continue_url($meta, $ctx);
        break;
}

redirect(new moodle_url($target));
