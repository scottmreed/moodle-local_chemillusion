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

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/chemillusion/card_edit.php', $cardid > 0 ? ['id' => $cardid] : []));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('card_edit_heading', 'local_chemillusion'));
$PAGE->set_heading(get_string('card_edit_heading', 'local_chemillusion'));

$form = new \local_chemillusion\form\graphical_card_form();

if ($card) {
    $front = json_decode($card->frontjson ?? '{}', true) ?? [];
    $form->set_data([
        'id'           => $card->id,
        'name'         => $card->name,
        'cardtype'     => $card->cardtype,
        'teacher_note' => $front['teacher_note'] ?? '',
    ]);
}

if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/chemillusion/graphical.php'));
}

if ($data = $form->get_data()) {
    // Build frontjson from form fields depending on card type.
    $front = ['teacher_note' => clean_param($data->teacher_note ?? '', PARAM_TEXT)];
    switch ($data->cardtype) {
        case 'newman_projection':
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
            $front['template'] = clean_param($data->rcd_template ?? '', PARAM_ALPHANUMEXT);
            $pts = json_decode($data->rcd_points_json ?? '[]', true);
            $front['points'] = is_array($pts) ? $pts : [];
            break;
        case 'orbital_hybridization':
            $front['smiles']    = clean_param($data->orbital_smiles ?? '', PARAM_RAW);
            $front['atom_idx']  = (int) ($data->orbital_atom_idx ?? -1);
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

    redirect(new moodle_url('/local/chemillusion/card_view.php', ['id' => $cardid]),
        get_string('card_saved', 'local_chemillusion'));
}

$PAGE->requires->js_call_amd('local_chemillusion/graphical_card_app', 'initEditor');

$output = $PAGE->get_renderer('local_chemillusion');
echo $output->header();
$form->display();
echo $output->footer();
