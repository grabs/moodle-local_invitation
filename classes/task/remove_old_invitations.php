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

namespace local_invitation\task;
use local_invitation\helper\date_time as datetime;
use local_invitation\helper\util as util;
use local_invitation\globals as gl;

defined('MOODLE_INTERNAL') || die();

/**
 * Observer class.
 *
 * @package    local_invitation
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class remove_old_invitations extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('remove_old_invitations', 'local_invitation');
    }

    /**
     * Run this task.
     */
    public function execute() {
        $DB = gl::db();

        // We want to remove all users after 12 hours. No user should be longer on this system.
        $timeend = datetime::floor_to_day(time()) - (datetime::DAY / 2);
        $params = array();
        $params['timeend'] = $timeend;

        $sql = "SELECT u.*
                FROM {local_invitation_users} iu
                    JOIN {user} u ON u.id = iu.userid
                WHERE iu.timecreated < :timeend
        ";
        mtrace('Remove old invitations ... ', '');
        util::remove_old_invitations();
        mtrace('done');

        mtrace('Remove expired users ...');
        if (!$users = $DB->get_records_sql($sql, $params)) {
            mtrace('... nothing to do.');
        } else {
            foreach ($users as $user) {
                mtrace('... delete user with id "'.$user->id.'" ...', '');
                util::anonymize_and_delete_user($user);
                mtrace('done');
            }
        }
        mtrace('done');
        return;
    }
}
