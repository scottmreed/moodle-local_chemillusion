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
 * Graphical card editor — create or edit a graphical card.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$cardid = optional_param('id', 0, PARAM_INT);
$requestedtool = optional_param('tool', '', PARAM_ALPHANUMEXT);

require_login();
$context = context_system::instance();

if ($cardid > 0) {
    $card = $DB->get_record('local_chemillusion_cards', ['id' => $cardid], '*', MUST_EXIST);
    if ($card->owneruserid == $USER->id) {
        require_capability('local/chemillusion:editowncards', $context);
    } else {
        require_capability('local/chemillusion:editallcards', $context);
    }
} else {
    require_capability('local/chemillusion:createcards', $context);
    $card = null;
}

$tool = null;
if ($card) {
    $tool = \local_chemillusion\cards\diagram_tool_catalog::get_by_cardtype($card->cardtype);
} else if ($requestedtool !== '') {
    $tool = \local_chemillusion\cards\diagram_tool_catalog::get($requestedtool);
}

$pageparams = [];
if ($cardid > 0) {
    $pageparams['id'] = $cardid;
} else if ($tool) {
    $pageparams['tool'] = $tool['id'];
}

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/chemillusion/card_edit.php', $pageparams));
$PAGE->set_pagelayout('standard');
$pagetitle = $tool
    ? get_string($tool['titlekey'], 'local_chemillusion')
    : get_string('card_edit_heading', 'local_chemillusion');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

$customdata = [];
if ($tool) {
    $customdata = [
        'toolid' => $tool['id'],
        'fixedcardtype' => $tool['cardtype'],
        'presetoptions' => \local_chemillusion\cards\diagram_tool_catalog::get_preset_options($tool['id']),
    ];
}
$form = new \local_chemillusion\form\graphical_card_form(null, $customdata);

if ($card) {
    $front = json_decode($card->frontjson ?? '{}', true) ?? [];
    $formdata = [
        'id'           => $card->id,
        'name'         => $card->name,
        'cardtype'     => $card->cardtype,
        'teacher_note' => $front['teacher_note'] ?? '',
    ];
    switch ($card->cardtype) {
        case 'newman_projection':
            $formdata += [
                'newman_preset' => $front['preset'] ?? 'butane_anti',
                'newman_front_a' => $front['front'][0] ?? 'CH3',
                'newman_front_b' => $front['front'][1] ?? 'H',
                'newman_front_c' => $front['front'][2] ?? 'H',
                'newman_back_a' => $front['back'][0] ?? 'CH3',
                'newman_back_b' => $front['back'][1] ?? 'H',
                'newman_back_c' => $front['back'][2] ?? 'H',
                'newman_rotation' => $front['rotation_degrees'] ?? 180,
                'newman_show_energy' => !empty($front['show_energy_hint']),
            ];
            break;
        case 'reaction_coordinate':
            $formdata += [
                'rcd_template' => $front['template'] ?? 'one_step_exergonic',
                'rcd_points_json' => json_encode($front['points'] ?? []),
            ];
            break;
        case 'orbital_hybridization':
            $formdata += [
                'orbital_template' => $front['template_id'] ?? 'ethene_pi_bond',
                'orbital_smiles' => $front['smiles'] ?? '',
                'orbital_atom_idx' => $front['atom_idx'] ?? 0,
            ];
            break;
        default:
            $formdata += [
                'smiles' => $front['smiles'] ?? '',
                'molecule_name' => $front['name'] ?? '',
                'group_id' => $front['group_id'] ?? '',
            ];
    }
    $form->set_data($formdata);
} else if ($tool) {
    $form->set_data(\local_chemillusion\cards\diagram_tool_catalog::get_editor_defaults($tool['id']));
}

if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/chemillusion/graphical.php'));
}

if ($data = $form->get_data()) {
    // Build frontjson from form fields depending on card type.
    $front = ['teacher_note' => clean_param($data->teacher_note ?? '', PARAM_TEXT)];
    switch ($data->cardtype) {
        case 'newman_projection':
            $front['preset']          = clean_param($data->newman_preset ?? '', PARAM_ALPHANUMEXT);
            $front['front']            = [
                clean_param($data->newman_front_a, PARAM_TEXT),
                clean_param($data->newman_front_b, PARAM_TEXT),
                clean_param($data->newman_front_c, PARAM_TEXT),
            ];
            $front['back']             = [
                clean_param($data->newman_back_a, PARAM_TEXT),
                clean_param($data->newman_back_b, PARAM_TEXT),
                clean_param($data->newman_back_c, PARAM_TEXT),
            ];
            $front['rotation_degrees'] = (int) ($data->newman_rotation ?? 0);
            $front['show_energy_hint'] = !empty($data->newman_show_energy);
            break;
        case 'reaction_coordinate':
            $templateid = clean_param($data->rcd_template ?? '', PARAM_ALPHANUMEXT);
            $template = \local_chemillusion\cards\reaction_coordinate_template_registry::default_card_data($templateid);
            if ($template) {
                $front += $template;
                unset($front['type']);
            }
            $front['template'] = $templateid;
            if (!empty($data->rcd_points_json)) {
                $pts = json_decode($data->rcd_points_json, true);
                if (is_array($pts) && count($pts) >= 2) {
                    $front['points'] = $pts;
                }
            }
            break;
        case 'orbital_hybridization':
            $templateid = clean_param($data->orbital_template ?? '', PARAM_ALPHANUMEXT);
            $template = \local_chemillusion\cards\orbital_template_registry::get($templateid);
            if ($template) {
                $front['template_id'] = $templateid;
                $front['description'] = $template['description'];
            }
            $front['smiles'] = clean_param($data->orbital_smiles ?? '', PARAM_RAW);
            $front['atom_idx'] = (int) ($data->orbital_atom_idx ?? 0);
            break;
        case 'reagent_card':
            $front['acronym']   = clean_param($data->reagent_acronym ?? '', PARAM_TEXT);
            $front['full_name'] = clean_param($data->reagent_full_name ?? '', PARAM_TEXT);
            $front['role']      = clean_param($data->reagent_role ?? '', PARAM_TEXT);
            break;
        default:
            $front['smiles'] = clean_param($data->smiles ?? '', PARAM_RAW);
            $front['name']   = clean_param($data->molecule_name ?? '', PARAM_TEXT);
            if (!empty($data->group_id)) {
                $front['group_id'] = clean_param($data->group_id, PARAM_ALPHANUMEXT);
            }
    }

    $now  = time();
    $rec  = new stdClass();
    $rec->cardtype          = $data->cardtype;
    $rec->name              = clean_param($data->name, PARAM_TEXT);
    $rec->frontjson         = json_encode($front);
    $rec->backjson          = '{}';
    $rec->renderjson        = '{}';
    $rec->accessibilityjson = '{}';
    $rec->updated_at        = $now;

    if ($cardid > 0) {
        $rec->id = $cardid;
        $DB->update_record('local_chemillusion_cards', $rec);
    } else {
        $rec->owneruserid = $USER->id;
        $rec->source      = 'local';
        $rec->sortorder   = 0;
        $rec->created_at  = $now;
        $cardid = $DB->insert_record('local_chemillusion_cards', $rec);
    }

    redirect(
        new moodle_url('/local/chemillusion/card_view.php', ['id' => $cardid]),
        get_string('card_saved', 'local_chemillusion')
    );
}

$editorconfig = [
    'toolid' => $tool['id'] ?? '',
    'presets' => $tool
        ? \local_chemillusion\cards\diagram_tool_catalog::get_preview_presets($tool['id'])
        : [],
];
$PAGE->requires->js_call_amd('local_chemillusion/graphical_card_app', 'initEditor', [$editorconfig]);

$output = $PAGE->get_renderer('local_chemillusion');
echo $output->header();
$form->display();
echo $output->footer();
