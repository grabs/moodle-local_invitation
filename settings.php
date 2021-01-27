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
 * @package    local_invitation
 * @author     Andreas Grabs <info@grabs-edv.de>
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_invitation\helper\date_time as datetime;
use local_invitation\helper\util as util;
use local_invitation\globals as gl;

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    $settings = new admin_settingpage('local_invitation', get_string('pluginname', 'local_invitation'));
    $ADMIN->add('localplugins', $settings);

    $configs = array();

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
        'deleteafterlogout',
        get_string('delete_after_logout', 'local_invitation'),
        get_string('delete_after_logout_help', 'local_invitation'),
        false
    );

    $options = \local_invitation\form\base::get_expiration_options();
    $configs[] = new admin_setting_configselect(
        'expiration',
        get_string('expiration_time', 'local_invitation'),
        get_string('expiration_time_help', 'local_invitation'),
        1,
        $options
    );

    $options = \local_invitation\form\base::get_maxusers_options(0);
    $configs[] = new admin_setting_configselect(
        'maxusers',
        get_string('max_users_per_invitation', 'local_invitation'),
        '',
        15,
        $options
    );

    $options = util::get_role_choices(CONTEXT_COURSE);
    $configs[] = new admin_setting_configselect(
        'userrole',
        get_string('userrole', 'local_invitation'),
        '',
        null,
        $options
    );

    $options = util::get_role_choices(CONTEXT_SYSTEM);
    $configs[] = new admin_setting_configselect(
        'systemrole',
        get_string('systemrole', 'local_invitation'),
        get_string('systemrole_help', 'local_invitation'),
        null,
        $options
    );

    $options = util::get_prevent_actions();
    $configs[] = new admin_setting_configmultiselect(
        'preventactions',
        get_string('preventactions', 'local_invitation'),
        '',
        array_keys($options),
        $options
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
