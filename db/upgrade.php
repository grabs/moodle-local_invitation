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
 * This file keeps track of upgrades to this plugin.
 *
 * @package    local_invitation
 * @author     Andreas Grabs <info@grabs-edv.de>
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @param mixed $oldversion
 */

/**
 * Upgrade the plugin depending on the old and the new version.
 *
 * @param  int  $oldversion
 * @return bool
 */
function xmldb_local_invitation_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2021012000) {
        // Define index secret (not unique) to be added to local_invitation.
        $table = new xmldb_table('local_invitation');
        $index = new xmldb_index('secret', XMLDB_INDEX_NOTUNIQUE, ['secret']);

        // Conditionally launch add index secret.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Define index timecreated (not unique) to be added to local_invitation_users.
        $table = new xmldb_table('local_invitation_users');
        $index = new xmldb_index('timecreated', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);

        // Conditionally launch add index timecreated.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        upgrade_plugin_savepoint(true, 2021012000, 'local', 'invitation');
    }

    if ($oldversion < 2021012001) {
        // Define field deleted to be added to local_invitation_users.
        $table = new xmldb_table('local_invitation_users');
        $field = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'timecreated');

        // Conditionally launch add field deleted.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Invitation savepoint reached.
        upgrade_plugin_savepoint(true, 2021012001, 'local', 'invitation');
    }

    return true;
}
