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
 * English language strings for local_chemillusion.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'ChemIllusion Study Cards';
$string['plugindescription'] = 'Privacy-aware chemistry study tools for Moodle: molecule lookup, RDKit.js structure rendering, functional-group highlighting, study flashcards, and optional ChemIllusion account linking.';

// Navigation / pages.
$string['nav_studytools'] = 'ChemIllusion study tools';
$string['dashboard_heading'] = 'ChemIllusion Study Cards';
$string['dashboard_intro'] = 'Free chemistry study tools inside Moodle. Look up molecules, build study decks, and learn functional groups and reagents.';
$string['tools_heading'] = 'Molecule lookup & study tools';
$string['cards_heading'] = 'Study decks';
$string['back_to_dashboard'] = '← Back to ChemIllusion';
$string['back_to_decks'] = '← Back to study decks';

// Settings: mode.
$string['settings_mode_heading'] = 'Operating mode';
$string['settings_mode_desc'] = 'Choose how much of ChemIllusion is enabled. Local-only keeps all study tools inside Moodle with no account required.';
$string['settings_mode'] = 'ChemIllusion mode';
$string['settings_mode_help'] = 'Local-only: study tools only. Local + linking: students/teachers can link a ChemIllusion account. Local + SaaS: enables premium ChemIllusion escalation.';
$string['mode_local_only'] = 'Local-only';
$string['mode_local_link'] = 'Local + ChemIllusion account linking';
$string['mode_local_saas'] = 'Local + ChemIllusion SaaS tools';

// Settings: external services.
$string['settings_external_heading'] = 'External services';
$string['settings_external_desc'] = 'Control which outbound calls the plugin may make. Administrators can disable every external call.';
$string['settings_enable_pubchem'] = 'Enable PubChem lookup';
$string['settings_enable_pubchem_help'] = 'Allow server-side molecule resolution via the public PubChem REST API. Results are cached locally.';
$string['settings_enable_account_linking'] = 'Enable ChemIllusion account linking';
$string['settings_enable_account_linking_help'] = 'Show CTAs that let users link or create a ChemIllusion account. No grades or rosters are ever sent.';
$string['settings_enable_visual_preview'] = 'Enable ChemIllusion visual preview examples';
$string['settings_enable_visual_preview_help'] = 'Show bundled example images of ChemIllusion-style visual study cards. No anonymous image generation occurs in Moodle.';
$string['settings_enable_conversion_metadata'] = 'Enable external analytics/conversion metadata';
$string['settings_enable_conversion_metadata_help'] = 'With consent, send minimal source metadata (no PII) to ChemIllusion when a user clicks a funnel CTA.';
$string['settings_enable_rdkit'] = 'Enable RDKit WASM local chemistry mode (Phase 1B)';
$string['settings_enable_rdkit_help'] = 'Lazy-load the bundled RDKit.js/WASM build on study tool pages for in-browser SMILES validation, rendering, and functional-group detection.';

// Settings: connection.
$string['settings_conn_heading'] = 'ChemIllusion connection';
$string['settings_conn_desc'] = 'Where ChemIllusion lives and how launch tokens are signed. The signing secret never reaches the browser.';
$string['settings_base_url'] = 'ChemIllusion base URL';
$string['settings_base_url_help'] = 'Base URL of the ChemIllusion site used for account linking and CTA links.';
$string['settings_signing_secret'] = 'Launch signing secret';
$string['settings_signing_secret_help'] = 'Shared secret used to sign account-link/launch state. Keep this private; it is never exposed in page output or JavaScript.';

// Settings: privacy.
$string['settings_privacy_heading'] = 'Privacy';
$string['settings_privacy_desc'] = 'Defaults favour minimal data. A data-flow summary is shown before ChemIllusion SaaS features are enabled.';
$string['settings_minimal_mode'] = 'Minimal mode (default)';
$string['settings_minimal_mode_help'] = 'Store only the minimum local data needed for study tools and account mapping.';
$string['settings_disable_external'] = 'Disable all external calls';
$string['settings_disable_external_help'] = 'Master kill-switch: when enabled, the plugin makes no outbound network calls at all.';
$string['settings_cache_ttl'] = 'Lookup cache lifetime (seconds)';
$string['settings_cache_ttl_help'] = 'How long PubChem lookup payloads are cached server-side. Default is one week (604800 seconds).';

// Capabilities.
$string['chemillusion:view'] = 'Use ChemIllusion study tools';
$string['chemillusion:managedecks'] = 'Create and manage ChemIllusion study decks';
$string['chemillusion:viewdashboard'] = 'View the ChemIllusion conversion dashboard';
$string['chemillusion:link'] = 'Link a ChemIllusion account';

// Lookup UI.
$string['lookup_label'] = 'Molecule name, SMILES, InChI, or InChIKey';
$string['lookup_placeholder'] = 'e.g. aspirin, CC(=O)Oc1ccccc1C(=O)O';
$string['lookup_button'] = 'Look up';
$string['lookup_inputtype'] = 'Detected input type: {$a}';
$string['result_name'] = 'Preferred name';
$string['result_cid'] = 'PubChem CID';
$string['result_formula'] = 'Molecular formula';
$string['result_mw'] = 'Molecular weight';
$string['result_canonical_smiles'] = 'Canonical SMILES';
$string['result_isomeric_smiles'] = 'Isomeric SMILES';
$string['result_inchikey'] = 'InChIKey';
$string['result_pubchem_link'] = 'View on PubChem';
$string['result_open_chemillusion'] = 'Open in ChemIllusion';
$string['result_textonly'] = 'Show text-only version';

// Decks / cards.
$string['deck_create'] = 'Create deck';
$string['deck_name'] = 'Deck name';
$string['deck_addcard'] = 'Add to deck';
$string['deck_empty'] = 'No decks yet. Create one to start studying.';
$string['deck_type_molecule'] = 'Molecule identity';
$string['deck_type_functional_group'] = 'Functional group recognition';
$string['deck_type_reagent'] = 'Reagent acronym';
$string['deck_type_name_smiles'] = 'Name to SMILES';
$string['deck_type_formula_mw'] = 'Formula / MW';
$string['flashcard_show_answer'] = 'Show answer';
$string['flashcard_next'] = 'Next card';
$string['flashcard_prev'] = 'Previous card';
$string['flashcard_progress'] = 'Card {$a->index} of {$a->total}';
$string['flashcard_textonly'] = 'Text-only version';

// Functional groups / reagents.
$string['functional_groups_heading'] = 'Detected functional groups';
$string['functional_group_highlight'] = 'Highlight';
$string['functional_group_makecard'] = 'Make flashcard';
$string['common_mistake'] = 'Common mistake';
$string['reagent_role'] = 'Role';
$string['reagent_use'] = 'Common use';

// CTAs / funnel.
$string['cta_continue_chemillusion'] = 'Continue in ChemIllusion';
$string['cta_save_deck'] = 'Save this deck to ChemIllusion';
$string['cta_visual_card'] = 'Generate a visual study card in ChemIllusion';
$string['cta_guided_workspace'] = 'Try the guided molecule workspace';
$string['cta_visual_card_blurb'] = 'Make this memorable in ChemIllusion: generate a visual study card with your molecule and functional group highlighted.';
$string['cta_teacher_account'] = 'Create a free ChemIllusion teacher account';
$string['cta_teacher_demo'] = 'Book a ChemIllusion demo';
$string['cta_teacher_convert'] = 'Convert this deck into a ChemIllusion guided activity';
$string['cta_teacher_visualset'] = 'Generate visual study cards for your class';
$string['cta_accessible_readout'] = 'Try accessible molecule readout';
$string['teacher_dashboard_blurb'] = 'Your students can use the local Moodle study cards for free. Link a ChemIllusion teacher account to create richer visual decks, guided tutorials, accessible molecule readouts, and course-ready activities.';

// Account linking.
$string['link_heading'] = 'Link your ChemIllusion account';
$string['link_start'] = 'Connect ChemIllusion';
$string['link_status_pending'] = 'Link pending';
$string['link_status_linked'] = 'Account linked';
$string['link_success'] = 'Your ChemIllusion account is now linked.';
$string['link_disabled'] = 'Account linking is not enabled on this site.';

// RDKit status.
$string['rdkit_loading'] = 'Loading local chemistry engine…';
$string['rdkit_ready'] = 'Local chemistry engine ready';
$string['rdkit_failed'] = 'Local chemistry engine unavailable; showing text and PubChem data instead.';
$string['rdkit_disabled'] = 'Local RDKit mode is disabled by the administrator.';

// Dashboard metrics.
$string['metric_lookups'] = 'Molecule lookups';
$string['metric_decks'] = 'Decks created';
$string['metric_sessions'] = 'Study sessions';
$string['metric_link_clicks'] = 'Account-link clicks';
$string['metric_demo_clicks'] = 'Teacher demo clicks';

// Privacy summary page.
$string['privacy_page_heading'] = 'ChemIllusion data-flow summary';
$string['privacy_page_intro'] = 'This page explains exactly what data the plugin stores and what (if anything) leaves your Moodle site.';

// Errors.
$string['error_nomatch'] = 'No molecule matched your search. Check the spelling or try a SMILES string.';
$string['error_ratelimited'] = 'The lookup service is busy. Please try again in a moment.';
$string['error_network'] = 'Could not reach the lookup service. Please try again later.';
$string['error_external_disabled'] = 'External lookups are disabled by the administrator.';
$string['error_invalidinput'] = 'Please enter a molecule name, SMILES, InChI, or InChIKey.';
$string['error_invalidsesskey'] = 'Your session has expired. Please reload the page and try again.';

// Tasks.
$string['purgeexpiredcachetask'] = 'Purge expired ChemIllusion lookup cache';

// Privacy API metadata.
$string['privacy:metadata:local_chemillusion_links'] = 'Minimal mapping between a Moodle user and a linked ChemIllusion account.';
$string['privacy:metadata:local_chemillusion_links:userid'] = 'The Moodle user who linked an account.';
$string['privacy:metadata:local_chemillusion_links:chemillusion_user_id'] = 'The opaque ChemIllusion account identifier.';
$string['privacy:metadata:local_chemillusion_links:chemillusion_email_hash'] = 'A one-way hash of the email used to link, if provided.';
$string['privacy:metadata:local_chemillusion_links:linked_at'] = 'When the account was linked.';
$string['privacy:metadata:local_chemillusion_links:last_launch_at'] = 'When the user last launched ChemIllusion.';
$string['privacy:metadata:local_chemillusion_decks'] = 'Study decks created by the user.';
$string['privacy:metadata:local_chemillusion_decks:userid'] = 'The user who created the deck.';
$string['privacy:metadata:local_chemillusion_decks:name'] = 'The deck name.';
$string['privacy:metadata:local_chemillusion_decks:created_at'] = 'When the deck was created.';
$string['privacy:metadata:local_chemillusion_cards'] = 'Cards within a user-created study deck.';
$string['privacy:metadata:local_chemillusion_cards:prompt'] = 'The card prompt text.';
$string['privacy:metadata:local_chemillusion_cards:answer'] = 'The card answer text.';
$string['privacy:metadata:chemillusion_saas'] = 'Information sent to ChemIllusion when a user explicitly links an account or clicks a funnel CTA (only if enabled).';
$string['privacy:metadata:chemillusion_saas:source'] = 'A static source label (for example, "moodle").';
$string['privacy:metadata:chemillusion_saas:role'] = 'The coarse role (student, teacher, or admin).';
$string['privacy:metadata:chemillusion_saas:surface'] = 'Which study surface triggered the action.';
