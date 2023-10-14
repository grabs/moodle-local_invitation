<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The settings definition of this plugin.
 * @package    local_invitation
 * @author     Andreas Grabs <info@grabs-edv.de>
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_invitation\helper\util;

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_invitation', get_string('pluginname', 'local_invitation'));
    $ADMIN->add('localplugins', $settings);

    $configs = [];

    $configs[] = new admin_setting_heading(
        'local_invitation',
        get_string('settings'),
        ''
    );

    $configs[] = new admin_setting_configcheckbox(
        'active',
        get_string('active'),
        '',
        false
    );

    $configs[] = new admin_setting_configcheckbox(
        'showinusernavigation',
        get_string('show_icon_in_usernavigation', 'local_invitation'),
        '',
        true
    );

    $configs[] = new admin_setting_configcheckbox(
        'deleteafterlogout',
        get_string('delete_after_logout', 'local_invitation'),
        get_string('delete_after_logout_help', 'local_invitation'),
        false
    );

    $options   = \local_invitation\form\base::get_expiration_options();
    $configs[] = new admin_setting_configselect(
        'expiration',
        get_string('expiration_time', 'local_invitation'),
        get_string('expiration_time_help', 'local_invitation'),
        1,
        $options
    );

    $options   = \local_invitation\form\base::get_maxusers_options(0);
    $configs[] = new admin_setting_configselect(
        'maxusers',
        get_string('max_users_per_invitation', 'local_invitation'),
        '',
        15,
        $options
    );

    $guestrole = get_guest_role();
    $options   = util::get_role_choices(CONTEXT_COURSE);
    $configs[] = new admin_setting_configselect(
        'userrole',
        get_string('userrole', 'local_invitation'),
        '',
        $guestrole->id,
        $options
    );

    $options   = util::get_role_choices(CONTEXT_SYSTEM);
    $configs[] = new admin_setting_configselect(
        'systemrole',
        get_string('systemrole', 'local_invitation'),
        get_string('systemrole_help', 'local_invitation'),
        $guestrole->id,
        $options
    );

    $configs[] = new admin_setting_configtextarea(
        'preventactions',
        get_string('preventactions', 'local_invitation'),
        get_string('preventactions_help', 'local_invitation'),
        util::get_default_prevent_actions(),
        PARAM_RAW,
        120, 8
    );

    $configs[] = new admin_setting_configcheckbox(
        'singlenamefield',
        get_string('single_name_field', 'local_invitation'),
        get_string('single_name_field_help', 'local_invitation'),
        false
    );

    $configs[] = new admin_setting_confightmleditor(
        'nameinfo',
        get_string('nameinfo', 'local_invitation'),
        get_string('nameinfo_help', 'local_invitation'),
        ''
    );

    $configs[] = new admin_setting_confightmleditor(
        'consent',
        get_string('consent', 'local_invitation'),
        get_string('consent_help', 'local_invitation'),
        ''
    );

    // Put all settings into the settings page.
    foreach ($configs as $config) {
        $config->plugin = 'local_invitation';
        $settings->add($config);
    }
}
