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
 * Admin settings for local_chemillusion.
 *
 * Mirrors PRD section 7.2.A: mode selection, external service toggles, and
 * privacy controls. Every external call can be disabled by an administrator.
 *
 * @package    local_chemillusion
 * @copyright  2026 MolLogic / Scott Reed
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_chemillusion',
        get_string('pluginname', 'local_chemillusion')
    );
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_heading(
        'local_chemillusion/infoheading',
        get_string('settings_info_heading', 'local_chemillusion'),
        get_string('settings_info_desc', 'local_chemillusion')
    ));

    $settings->add(new admin_setting_heading(
        'local_chemillusion/modeheading',
        get_string('settings_mode_heading', 'local_chemillusion'),
        get_string('settings_mode_desc', 'local_chemillusion')
    ));

    $settings->add(new admin_setting_configselect(
        'local_chemillusion/mode',
        get_string('settings_mode', 'local_chemillusion'),
        get_string('settings_mode_help', 'local_chemillusion'),
        'local_only',
        [
            'local_only' => get_string('mode_local_only', 'local_chemillusion'),
            'local_link' => get_string('mode_local_link', 'local_chemillusion'),
            'local_saas' => get_string('mode_local_saas', 'local_chemillusion'),
        ]
    ));

    $settings->add(new admin_setting_heading(
        'local_chemillusion/extheading',
        get_string('settings_external_heading', 'local_chemillusion'),
        get_string('settings_external_desc', 'local_chemillusion')
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_chemillusion/enable_pubchem',
        get_string('settings_enable_pubchem', 'local_chemillusion'),
        get_string('settings_enable_pubchem_help', 'local_chemillusion'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_chemillusion/enable_account_linking',
        get_string('settings_enable_account_linking', 'local_chemillusion'),
        get_string('settings_enable_account_linking_help', 'local_chemillusion'),
        0
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_chemillusion/enable_visual_preview',
        get_string('settings_enable_visual_preview', 'local_chemillusion'),
        get_string('settings_enable_visual_preview_help', 'local_chemillusion'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_chemillusion/enable_conversion_metadata',
        get_string('settings_enable_conversion_metadata', 'local_chemillusion'),
        get_string('settings_enable_conversion_metadata_help', 'local_chemillusion'),
        0
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_chemillusion/enable_rdkit',
        get_string('settings_enable_rdkit', 'local_chemillusion'),
        get_string('settings_enable_rdkit_help', 'local_chemillusion'),
        1
    ));

    $settings->add(new admin_setting_heading(
        'local_chemillusion/connheading',
        get_string('settings_conn_heading', 'local_chemillusion'),
        get_string('settings_conn_desc', 'local_chemillusion')
    ));

    $settings->add(new admin_setting_configtext(
        'local_chemillusion/chemillusion_base_url',
        get_string('settings_base_url', 'local_chemillusion'),
        get_string('settings_base_url_help', 'local_chemillusion'),
        'https://chemillusion.com',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'local_chemillusion/signing_secret',
        get_string('settings_signing_secret', 'local_chemillusion'),
        get_string('settings_signing_secret_help', 'local_chemillusion'),
        ''
    ));

    $settings->add(new admin_setting_heading(
        'local_chemillusion/privheading',
        get_string('settings_privacy_heading', 'local_chemillusion'),
        get_string('settings_privacy_desc', 'local_chemillusion')
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_chemillusion/minimal_mode',
        get_string('settings_minimal_mode', 'local_chemillusion'),
        get_string('settings_minimal_mode_help', 'local_chemillusion'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_chemillusion/disable_external',
        get_string('settings_disable_external', 'local_chemillusion'),
        get_string('settings_disable_external_help', 'local_chemillusion'),
        0
    ));

    $settings->add(new admin_setting_configtext(
        'local_chemillusion/cache_ttl',
        get_string('settings_cache_ttl', 'local_chemillusion'),
        get_string('settings_cache_ttl_help', 'local_chemillusion'),
        '604800',
        PARAM_INT
    ));

    $settings->add(new admin_setting_heading(
        'local_chemillusion/graphicheading',
        get_string('settings_graphical_heading', 'local_chemillusion'),
        get_string('settings_graphical_desc', 'local_chemillusion')
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_chemillusion/enable_graphical_cards',
        get_string('settings_enable_graphical_cards', 'local_chemillusion'),
        get_string('settings_enable_graphical_cards_help', 'local_chemillusion'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_chemillusion/enable_newman_cards',
        get_string('settings_enable_newman_cards', 'local_chemillusion'),
        get_string('settings_enable_newman_cards_help', 'local_chemillusion'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_chemillusion/enable_orbital_cards',
        get_string('settings_enable_orbital_cards', 'local_chemillusion'),
        get_string('settings_enable_orbital_cards_help', 'local_chemillusion'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_chemillusion/enable_reaction_coordinate_cards',
        get_string('settings_enable_reaction_coordinate_cards', 'local_chemillusion'),
        get_string('settings_enable_reaction_coordinate_cards_help', 'local_chemillusion'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_chemillusion/enable_card_exports',
        get_string('settings_enable_card_exports', 'local_chemillusion'),
        get_string('settings_enable_card_exports_help', 'local_chemillusion'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_chemillusion/enable_png_export',
        get_string('settings_enable_png_export', 'local_chemillusion'),
        get_string('settings_enable_png_export_help', 'local_chemillusion'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_chemillusion/enable_chemillusion_visual_card_cta',
        get_string('settings_enable_chemillusion_visual_card_cta', 'local_chemillusion'),
        get_string('settings_enable_chemillusion_visual_card_cta_help', 'local_chemillusion'),
        0
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_chemillusion/default_accessible_summary_visible',
        get_string('settings_default_accessible_summary_visible', 'local_chemillusion'),
        get_string('settings_default_accessible_summary_visible_help', 'local_chemillusion'),
        1
    ));
}
