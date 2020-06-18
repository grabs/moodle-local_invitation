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

namespace local_invitation\helper;
use local_invitation\helper\date_time as datetime;
use local_invitation\globals as gl;

defined('MOODLE_INTERNAL') || die();

class util {
    /**
     * Get all roles as choice parameters.
     * Because we need them more than once so we define it here.
     *
     * @return array
     */
    public static function get_role_choices() {
        static $choices;

        if (empty($choices)) {
            $roles = get_all_roles();
            $choices = role_fix_names($roles, null, ROLENAME_ORIGINAL, true);
            $choices = array(0 => get_string('choose')) + $choices;
        }
        return $choices;
    }

    public static function generate_secret_for_inventation() {
        $DB = gl::db();

        $secret = generate_uuid();
        while ($DB->count_records('local_invitation', array('secret' => $secret)) > 0) {
            $secret = generate_uuid();
        }
        return $secret;
    }

    public static function get_invitation_from_secret($secret, $courseid) {
        $DB = gl::db();

        $params = array();
        $params['courseid'] = $courseid;
        $params['secret'] = $secret;
        $params['now1'] = time();
        $params['now2'] = $params['now1'];

        $sql = "SELECT i.*
                FROM {local_invitation} i
                    JOIN {course} c ON c.id = i.courseid
                WHERE i.secret = :secret AND
                    i.timestart <= :now1 AND
                    i.timeend > :now2 AND
                    (
                        SELECT COUNT(*)
                        FROM {local_invitation_users} iu
                        WHERE iu.invitationid = i.id
                    ) < i.maxusers
        ";
        $invitation = $DB->get_record_sql($sql, $params);

        return $invitation;
    }

    public static function create_login_and_enrol($invitation, $confirmdata) {
        $DB = gl::db();

        // Wrap the SQL queries in a transaction.
        $transaction = $DB->start_delegated_transaction();

        try {
            $newuser = self::create_login($invitation->secret, $confirmdata->firstname, $confirmdata->lastname);
            self::enrol_user($invitation->courseid, $invitation->userrole, $newuser);
        } catch (\moodle_exception $e) {
            return false;
        }

        // We should be good to go now.
        $transaction->allow_commit();

        // The user exists and we can now login him.
        $user = authenticate_user_login($newuser->username, $newuser->password_raw);
        complete_user_login($user);
        return $user;
    }

    /**
     * Create a new user to login into the democourse.
     * @return \stdClass the new created user
     */
    private static function create_login($secret, $firstname, $lastname) {
        $DB = gl::db();
        $CFG = gl::cfg();

        require_once($CFG->dirroot.'/user/lib.php');

        $user = new \stdClass();
        $user->username = self::get_free_username('invited_');
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->password_raw = generate_password();
        $user->password = hash_internal_user_password($user->password_raw, true);
        $user->deleted = 0;
        $user->confirmed = 1;
        $user->timemodified = time();
        $user->timecreated = time();
        $user->suspended = 0;
        $user->auth = 'manual';
        $user->email = $user->username.'@'.$secret;
        $user->lang = 'de';
        $user->id = user_create_user($user, false);

        if (empty($user->id)) {
            throw new \moodle_exception('Could not create new user');
        }

        // Make sure user context exists.
        \context_user::instance($user->id);

        return $user;
    }

    /**
     * This enrols the given user into the course of $this->demologin->democourseid.
     * @param int $courseid
     * @param \stdClass $user
     * @return void
     */
    private static function enrol_user($courseid, $roleid, $user) {
        $manual = enrol_get_plugin('manual');

        $coursecontext = \context_course::instance($courseid);
        if ($instances = enrol_get_instances($courseid, false)) {
            foreach ($instances as $instance) {
                if ($instance->enrol === 'manual') {
                    break;
                }
            }
        }
        $enroleendtime = time() + datetime::DAY;
        $manual->enrol_user($instance, $user->id, $roleid, 0, $enroleendtime);

    }

    /**
     * Get a not used username.
     * @return string the new username
     */
    private static function get_free_username($prefix) {
        $DB = gl::db();

        $username = $prefix.random_string();
        $username = clean_param($username, PARAM_USERNAME);
        while ($DB->record_exists('user', array('username' => $username))) {
            $username = $prefix.random_string();
            $username = clean_param($username, PARAM_USERNAME);
        }
        return $username;
    }

    public static function require_active() {
        $cfg = get_config('local_invitation');
        if (empty($cfg->active)) {
            throw new \moodle_exception('error_invitation_not_active', 'local_invitation');
        }
    }

    public static function set_all_users_expired() {
        $DB = gl::db();

        $sql = "UPDATE {local_invitation_users} SET timecreated = 0";
        $DB->execute($sql);
    }

    public static function anonymize_and_delete_expired_users($tracing = false) {
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
        if ($tracing) {
            mtrace('Remove expired users ...');
        }
        if (!$users = $DB->get_records_sql($sql, $params)) {
            if ($tracing) {
                mtrace('... nothing to do.');
            }
        } else {
            foreach ($users as $user) {
                if ($tracing) {
                    mtrace('... delete user with id "'.$user->id.'" ...', '');
                }
                self::anonymize_and_delete_user($user);
                if ($tracing) {
                    mtrace('done');
                }
            }
        }
        if ($tracing) {
            mtrace('done');
        }
    }

    public static function anonymize_and_delete_user($user) {
        $DB = gl::db();

        $user->firstname = '-';
        $user->lastname = '-';
        $DB->update_record('user', $user);
        delete_user($user);

        $DB->delete_records('local_invitation_users', array('userid' => $user->id));

        return;
    }

    /**
     * Remove expired invitations and those which has an invalid course id
     *
     * @return void
     */
    public static function remove_old_invitations($tracing = false) {
        $DB = gl::db();

        if ($tracing) {
            mtrace('Remove old invitations ... ');
        }

        $params = array('now' => time());
        $sql = "SELECT i.*
                FROM {local_invitation} i
                    LEFT JOIN {course} c ON c.id = i.courseid
                WHERE c.id IS NULL OR i.timeend < :now
        ";

        if (!$invitations = $DB->get_records_sql($sql, $params)) {
            if ($tracing) {
                mtrace('... nothing to do.');
                mtrace('done');
            }
            return;
        }

        $count = count($invitations);
        if ($tracing) {
            mtrace('... found '.$count.' expired invitations');
        }

        foreach ($invitations as $invitation) {
            $DB->delete_records('local_invitation', array('id' => $invitation->id));
        }
        if ($tracing) {
            mtrace('done');
        }
    }
}
