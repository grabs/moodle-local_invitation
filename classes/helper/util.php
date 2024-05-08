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

namespace local_invitation\helper;

use local_invitation\globals as gl;
use local_invitation\helper\date_time as datetime;

/**
 * Utility class.
 *
 * @package    local_invitation
 * @author     Andreas Grabs <info@grabs-edv.de>
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class util {
    /** Default patterns for prevent actions. */
    public const PREVENTPATTERNS = [
        'enrolment'         => '#enrol/index.php#',
        'courselist'        => '#course(/index.php.*|/)$#',
        'calendar'          => '#calendar/#',
        'gradebook'         => '#grade/#',
        'coursesearch'      => '#course/search.php#',
        'coursejump'        => '#blocks/course_jump#',
        'profile'           => '#user/profile.php#',
        'managetoken'       => '#user/managetoken.php#',
        'userpreferences'   => '#user/preferences.php#',
        'badges'            => '#badges/.*#',
        'messages'          => '#message/index.php#',
    ];

    /**
     * Get all roles as choice parameters.
     * Because we need them more than once so we define it here.
     *
     * @param  int   $contextlevel the contextlevel the roles have to be assignable
     * @return array
     */
    public static function get_role_choices($contextlevel) {
        $roles                 = self::get_roles_for_contextlevel($contextlevel);
        $guestrole             = get_guest_role();
        $roles[$guestrole->id] = $guestrole; // Add guest role to the list.

        $choices = role_fix_names($roles, null, ROLENAME_ORIGINAL, true);
        $choices = [0 => get_string('choose')] + $choices;

        return $choices;
    }

    /**
     * Get all roles.
     * Because we need them more than once so we define it here.
     *
     * @param  int   $contextlevel the contextlevel the roles have to be assignable
     * @return array the array with role records
     */
    public static function get_roles_for_contextlevel($contextlevel) {
        $DB = gl::db();

        $sql = 'SELECT r.*
                FROM {role} r
                    JOIN {role_context_levels} rcl
                        ON r.id = rcl.roleid
                WHERE rcl.contextlevel = :contextlevel
        ';
        $params = ['contextlevel' => $contextlevel];

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Generate a secret used by an invitation.
     *
     * @return string
     */
    public static function generate_secret_for_inventation() {
        $DB = gl::db();

        $secret = \core\uuid::generate();
        while ($DB->count_records('local_invitation', ['secret' => $secret]) > 0) {
            $secret = \core\uuid::generate();
        }

        return $secret;
    }

    /**
     * Create an invitation in the database.
     *
     * @param  \stdClass $invitedata
     * @return bool|int
     */
    public static function create_invitation($invitedata) {
        $DB = gl::db();

        $DB->delete_records('local_invitation', ['courseid' => $invitedata->courseid]);
        $invitedata->timemodified = time();
        $invitedata->secret       = self::generate_secret_for_inventation();

        return $DB->insert_record('local_invitation', $invitedata);
    }

    /**
     * Update an invitation.
     *
     * @param  \stdClass $invitation
     * @param  \stdClass $updatedata
     * @return bool
     */
    public static function update_invitation($invitation, $updatedata) {
        $DB = gl::db();

        $invitation->timestart = $updatedata->timestart;
        $invitation->timeend   = $updatedata->timeend;
        $invitation->maxusers  = $updatedata->maxusers;

        return $DB->update_record('local_invitation', $invitation);
    }

    /**
     * Delete an invitation.
     *
     * @param  int  $invitationid
     * @return bool
     */
    public static function delete_invitation($invitationid) {
        $DB = gl::db();

        return $DB->delete_records('local_invitation', ['id' => $invitationid]);
    }

    /**
     * Get an invitation using its secret.
     *
     * @param  string    $secret
     * @param  int       $courseid
     * @return \stdClass
     */
    public static function get_invitation_from_secret($secret, $courseid) {
        $DB = gl::db();

        $params              = [];
        $params['courseid']  = $courseid;
        $params['secret']    = $secret;
        $params['now1']      = time();
        $params['now2']      = $params['now1'];
        $params['unlimited'] = 0;

        $sql = 'SELECT i.*
                FROM {local_invitation} i
                    JOIN {course} c ON c.id = i.courseid
                    WHERE i.secret = :secret AND ((
                        i.timestart <= :now1 AND
                        i.timeend > :now2 AND (
                                SELECT COUNT(*)
                                FROM {local_invitation_users} iu
                                WHERE iu.invitationid = i.id
                            ) < i.maxusers
                        ) OR
                        i.maxusers = :unlimited
                    )
        ';
        $invitation = $DB->get_record_sql($sql, $params);

        return $invitation;
    }

    /**
     * Create a new temporary user, log in and enrol him.
     *
     * @param  \stdClass      $invitation
     * @param  \stdClass      $confirmdata
     * @return \stdClass|bool The new user record or false
     */
    public static function create_login_and_enrol($invitation, $confirmdata) {
        $DB    = gl::db();
        $mycfg = gl::mycfg();
        $CFG   = gl::cfg();

        // We don't want to send a welcome message to the dummy user.
        // The only way, I found, to prevent this message, is to remove the coursecontact from the $CFG variable.
        $CFG->coursecontact = null;

        // Wrap the SQL queries in a transaction.
        $transaction = $DB->start_delegated_transaction();

        try {
            $newuser = self::create_login($confirmdata->firstname, $confirmdata->lastname);
            self::enrol_user($invitation->courseid, $invitation->userrole, $newuser);
        } catch (\moodle_exception $e) {
            return false;
        }

        // We should be good to go now.
        $transaction->allow_commit();

        // The user exists and we can now login him.
        $user = authenticate_user_login($newuser->username, $newuser->password_raw);
        if (PHPUNIT_TEST) {
            // Hide session header errors in unit test.
            @complete_user_login($user);
        } else {
            complete_user_login($user);
        }

        // If there is an acceptance button in the login form we do the consent riht here.
        if (!empty($confirmdata->consent)) {
            // Get all policies with acceptance.
            if ($policies = \tool_policy\api::get_policies_with_acceptances($newuser->id)) {
                foreach ($policies as $policy) {
                    foreach ($policy->versions as $version) {
                        \tool_policy\api::accept_policies($version->id, $newuser->id);
                    }
                }
            }
        }

        if (!empty($mycfg->systemrole)) {
            role_assign($mycfg->systemrole, $user->id, \context_system::instance());
        }

        // Log this user in our table.
        $newuserrecord               = new \stdClass();
        $newuserrecord->invitationid = $invitation->id;
        $newuserrecord->userid       = $user->id;
        $newuserrecord->timecreated  = time();
        $DB->insert_record('local_invitation_users', $newuserrecord);

        return $user;
    }

    /**
     * Create a new user to login into the democourse.
     *
     * @param  string    $firstname
     * @param  string    $lastname
     * @return \stdClass the new created user
     */
    private static function create_login($firstname, $lastname) {
        $CFG = gl::cfg();

        require_once($CFG->dirroot . '/user/lib.php');

        $user               = new \stdClass();
        $user->username     = self::get_free_username('invited_');
        $user->firstname    = $firstname;
        $user->lastname     = $lastname;
        $user->mnethostid   = $CFG->mnet_localhost_id;
        $user->password_raw = generate_password();
        $user->password     = hash_internal_user_password($user->password_raw, true);
        $user->deleted      = 0;
        $user->confirmed    = 1;
        $user->timemodified = time();
        $user->timecreated  = time();
        $user->suspended    = 0;
        $user->auth         = 'manual';
        $user->email        = $user->username . '@' . self::get_email_domain();
        $user->lang         = $CFG->lang; // We use the system default language. Courses can have there own lang setting.
        $user->id           = user_create_user($user, false);

        if (empty($user->id)) {
            throw new \moodle_exception('Could not create new user');
        }

        // Make sure user context exists.
        \context_user::instance($user->id);

        return $user;
    }

    /**
     * This enrols the given user into the course of $this->demologin->democourseid.
     * @param  int       $courseid
     * @param  int       $roleid
     * @param  \stdClass $user
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
     *
     * @param  string $prefix
     * @return string the new username
     */
    private static function get_free_username($prefix) {
        $DB = gl::db();

        $username = $prefix . random_string();
        $username = clean_param($username, PARAM_USERNAME);
        while ($DB->record_exists('user', ['username' => $username])) {
            $username = $prefix . random_string();
            $username = clean_param($username, PARAM_USERNAME);
        }

        return $username;
    }

    /**
     * Get a temporary email domain.
     * @return string the email domain
     */
    private static function get_email_domain() {
        $DB = gl::db();

        $domain = random_string();
        $domain .= '.invalid'; // Use "invalid" as top level domain to prevent sending emails.

        return $domain;
    }

    /**
     * Is the plugin activated?
     *
     * @return bool
     */
    public static function is_active() {
        $cfg = get_config('local_invitation');

        return (bool) $cfg->active;
    }

    /**
     * Is the given user an invited user?
     *
     * @param  int  $userid
     * @return bool
     */
    public static function is_user_invited($userid) {
        $DB = gl::db();

        if ($DB->record_exists('local_invitation_users', ['userid' => $userid])) {
            return $DB->get_record('user', ['id' => $userid]);
        }
    }

    /**
     * Get the consent string from settings.
     *
     * @return string
     */
    public static function get_consent() {
        $mycfg   = gl::mycfg();
        $consent = $mycfg->consent;

        return $consent;
    }

    /**
     * If not activated an error is thrown.
     *
     * @return void
     */
    public static function require_active() {
        if (!self::is_active()) {
            throw new \moodle_exception('error_invitation_not_active', 'local_invitation');
        }
    }

    /**
     * Set all users as expired.
     *
     * @return void
     */
    public static function set_all_users_expired() {
        $DB = gl::db();

        $sql = 'UPDATE {local_invitation_users} SET timecreated = 0';
        $DB->execute($sql);
    }

    /**
     * Delete expired users and anonymize them before deleting.
     *
     * @param  bool $tracing
     * @return void
     */
    public static function anonymize_and_delete_expired_users($tracing = false) {
        $DB    = gl::db();
        $mycfg = gl::mycfg();

        // First clean old records of already deleted users.
        self::remove_deleted_users();

        $expiration = empty($mycfg->expiration) ? 1 : $mycfg->expiration;
        $expiration *= datetime::DAY;

        // We want to remove all users after x days defined in settings. No user should be longer on this system.
        $timeend           = time() - $expiration;
        $params            = [];
        $params['timeend'] = $timeend;

        $sql = 'SELECT u.*
                FROM {local_invitation_users} iu
                    JOIN {user} u ON u.id = iu.userid
                WHERE iu.timecreated < :timeend AND u.deleted = 0
        ';
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
                    mtrace('... delete user with id "' . $user->id . '" ...', '');
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

    /**
     * Remove deleted users from our table. Just to clean up things.
     *
     * @return void
     */
    public static function remove_deleted_users() {
        $DB = gl::db();

        $sql = 'SELECT u.*
                FROM {local_invitation_users} iu
                    JOIN {user} u ON u.id = iu.userid AND u.deleted = 1
        ';

        if (!$users = $DB->get_records_sql($sql, null)) {
            return;
        }

        foreach ($users as $user) {
            $DB->delete_records('local_invitation_users', ['id' => $user->id]);
        }
    }

    /**
     * Anonymize and delete the given user.
     *
     * @param  \stdClass $user
     * @return void
     */
    public static function anonymize_and_delete_user($user) {
        $DB = gl::db();

        $user->firstname = '-';
        $user->lastname  = '-';
        $DB->update_record('user', $user);
        delete_user($user);

        $DB->set_field('local_invitation_users', 'deleted', 1, ['userid' => $user->id]);
    }

    /**
     * Remove expired invitations and those which has an invalid course id.
     *
     * @param  bool $tracing
     * @return void
     */
    public static function remove_old_invitations($tracing = false) {
        $DB    = gl::db();
        $mycfg = gl::mycfg();

        if ($tracing) {
            mtrace('Remove old invitations ... ');
        }

        // Delete old invitation users.
        $expiration = empty($mycfg->expiration) ? 1 : $mycfg->expiration;
        $expiration *= datetime::DAY;
        $timeend = datetime::floor_to_day(time()) - $expiration;
        // Get all invitation users who are deleted not having an invitation anymore and delete them.
        $sql = 'SELECT ui.id, ui.timecreated
                FROM {local_invitation_users} ui
                    LEFT JOIN {local_invitation} i ON i.id = ui.invitationid
                WHERE i.id IS NULL AND
                    ui.timecreated < :timeend AND
                    ui.deleted = 1
        ';
        $params = ['timeend' => $timeend];
        $iusers = $DB->get_recordset_sql($sql, $params);
        foreach ($iusers as $iu) {
            $DB->delete_records('local_invitation_users', ['id' => $iu->id]);
        }

        // Get all old or invalid invitations and delete them.
        $params = ['now' => time()];
        $sql    = 'SELECT i.*
                FROM {local_invitation} i
                    LEFT JOIN {course} c ON c.id = i.courseid
                WHERE c.id IS NULL OR i.timeend < :now
        ';

        if (!$invitations = $DB->get_records_sql($sql, $params)) {
            if ($tracing) {
                mtrace('... nothing to do.');
                mtrace('done');
            }

            return;
        }

        $count = count($invitations);
        if ($tracing) {
            mtrace('... found ' . $count . ' expired invitations');
        }

        foreach ($invitations as $invitation) {
            $DB->delete_records('local_invitation', ['id' => $invitation->id]);
        }
        if ($tracing) {
            mtrace('done');
        }
    }

    /**
     * Check if the current url is some of the prevent actions. If so we redirect the user to the default homepage.
     *
     * @param  \stdClass $user
     * @return void
     */
    public static function prevent_actions($user) {
        global $FULLME;
        $mycfg = gl::mycfg();

        if (empty($mycfg->preventactions)) {
            return;
        }

        $COURSE = gl::course();

        if (!self::is_user_invited($user->id)) {
            return;
        }

        $preventactions = str_replace("\r", "\n", $mycfg->preventactions);
        $preventactions = str_replace("\n\n", "\n", $preventactions);
        $preventactions = explode("\n", $preventactions);

        foreach ($preventactions as $action) {
            $action = trim($action);
            if (empty($action)) {
                continue;
            }
            $pattern = '~' . $action . '~';
            if (preg_match($pattern, $FULLME)) {
                $context = \context_course::instance($COURSE->id);
                if (is_enrolled($context, $user)) {
                    $url = new \moodle_url('/course/view.php', ['id' => $COURSE->id]);
                } else {
                    $url = new \moodle_url('/');
                }
                redirect($url);
            }
        }
    }

    /**
     * Get the default prevent actions using in settings.
     *
     * @return string
     */
    public static function get_default_prevent_actions() {
        $preventactions = [
            'enrol/index.php',
            'course(/index.php.*|/)$',
            'calendar/',
            'grade/',
            'course/search.php',
            'user/files.php',
            'user/profile.php',
            'user/managetoken.php',
            'user/preferences.php',
            'badges/.*',
            'message/index.php',
        ];

        return implode("\n", $preventactions);
    }

    /**
     * Get a notification text combined from two strings.
     *
     * @return string
     */
    public static function get_invitation_note() {
        $mycfg = gl::mycfg();

        $invitationnote1 = get_string('invitation_note', 'local_invitation');

        $expiration = empty($mycfg->expiration) ? 1 : $mycfg->expiration;
        if ($expiration == 1) { // This means exactly one day!
            $expirationnote = '24 ' . get_string('hours');
        } else {
            $expirationnote = $expiration . ' ' . get_string('days');
        }

        if (!empty($mycfg->deleteafterlogout)) {
            $invitationnote2 = get_string(
                'invitation_delete_note_timeandlogout',
                'local_invitation',
                $expirationnote
            );
        } else {
            $invitationnote2 = get_string(
                'invitation_delete_note_timeonly',
                'local_invitation',
                $expirationnote
            );
        }

        return $invitationnote1 . ' ' . $invitationnote2;
    }
}
