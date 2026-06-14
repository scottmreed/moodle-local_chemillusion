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

namespace local_chemillusion\cards;

/**
 * Builds study card structures from molecules, functional groups, and reagents.
 *
 * Cards are plain arrays with cardtype, prompt, answer, and an optional
 * molecule_payload (JSON-encoded by the caller before storage).
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class card_generator {

    /**
     * Build a molecule identity card from a PubChem payload.
     *
     * @param array $payload Normalised molecule payload.
     * @return array
     */
    public static function from_molecule(array $payload) {
        $name = !empty($payload['name']) ? $payload['name'] : ($payload['canonical_smiles'] ?? '');
        $answerlines = [];
        if (!empty($payload['formula'])) {
            $answerlines[] = 'Formula: ' . $payload['formula'];
        }
        if (!empty($payload['mw'])) {
            $answerlines[] = 'MW: ' . $payload['mw'];
        }
        if (!empty($payload['cid'])) {
            $answerlines[] = 'CID: ' . $payload['cid'];
        }
        if (!empty($payload['canonical_smiles'])) {
            $answerlines[] = 'SMILES: ' . $payload['canonical_smiles'];
        }
        if (!empty($payload['inchikey'])) {
            $answerlines[] = 'InChIKey: ' . $payload['inchikey'];
        }
        return [
            'cardtype'         => 'molecule_identity',
            'prompt'           => $name,
            'answer'           => implode("\n", $answerlines),
            'molecule_payload' => $payload,
        ];
    }

    /**
     * Build a functional-group recognition card.
     *
     * @param string $id Group id.
     * @param array $group Group definition.
     * @return array
     */
    public static function from_functional_group($id, array $group) {
        $label = isset($group['label']) ? $group['label'] : $id;
        $answer = $label;
        if (!empty($group['student_summary'])) {
            $answer .= "\n" . $group['student_summary'];
        }
        if (!empty($group['common_mistake'])) {
            $answer .= "\nCommon mistake: " . $group['common_mistake'];
        }
        return [
            'cardtype'         => 'functional_group',
            'prompt'           => 'Identify the highlighted functional group.',
            'answer'           => $answer,
            'molecule_payload' => ['group' => $id, 'smarts' => $group['smarts'] ?? ''],
        ];
    }

    /**
     * Build a reagent acronym card.
     *
     * @param string $acronym
     * @param array $data Reagent definition.
     * @return array
     */
    public static function from_reagent($acronym, array $data) {
        $answerlines = [];
        if (!empty($data['name'])) {
            $answerlines[] = $data['name'];
        }
        if (!empty($data['role'])) {
            $answerlines[] = 'Role: ' . $data['role'];
        }
        if (!empty($data['common_use'])) {
            $answerlines[] = $data['common_use'];
        }
        return [
            'cardtype'         => 'reagent',
            'prompt'           => $acronym,
            'answer'           => implode("\n", $answerlines),
            'molecule_payload' => null,
        ];
    }

    /**
     * Validate and sanitise a client-supplied card before storage.
     * Browser-generated payloads are untrusted.
     *
     * @param array $card
     * @return array Clean card ready for deck_repository::add_card().
     */
    public static function sanitize(array $card) {
        $type = isset($card['cardtype']) ? clean_param($card['cardtype'], PARAM_ALPHANUMEXT) : 'custom';
        $prompt = isset($card['prompt']) ? clean_param($card['prompt'], PARAM_TEXT) : '';
        $answer = isset($card['answer']) ? clean_param($card['answer'], PARAM_TEXT) : '';
        $payload = null;
        if (isset($card['molecule_payload']) && is_array($card['molecule_payload'])) {
            // Keep only scalar leaf values.
            $payload = [];
            foreach ($card['molecule_payload'] as $k => $v) {
                if (is_scalar($v)) {
                    $payload[clean_param($k, PARAM_ALPHANUMEXT)] = clean_param((string) $v, PARAM_TEXT);
                }
            }
        }
        return [
            'cardtype'         => $type,
            'prompt'           => $prompt,
            'answer'           => $answer,
            'molecule_payload' => $payload,
        ];
    }
}
