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
 * Join page for invited users.
 * @package    local_invitation
 * @author     Andreas Grabs <info@grabs-edv.de>
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_invitation\globals as gl;
use local_invitation\helper\util;

// We do not have a login check in this file because the login is actually done here.
// So we have to ignore the codingstyle for the config.php inclusion which normally requires a login check.
// @codingStandardsIgnoreLine
require_once(__DIR__ . '/../../config.php');

util::require_active();

$courseid = required_param('courseid', PARAM_INT);
$secret   = required_param('id', PARAM_TEXT);

$DB     = gl::db();
$USER   = gl::user();
$FULLME = gl::fullme();

// Because it is an enrolment we use the system context.
$context = context_system::instance();

// First we check the courseid and the secret and the availability dates.
$invitation = util::get_invitation_from_secret($secret, $courseid);
if (!$invitation) {
    $errmsg = get_string('error_invalid_invitation', 'local_invitation');
    redirect(new \moodle_url('/local/invitation/error.php'), $errmsg, null, \core\output\notification::NOTIFY_ERROR);
}

$title = get_string('invitation', 'local_invitation');

$myurl = new \moodle_url($FULLME);
$myurl->remove_all_params();
$myurl->param('courseid', $courseid);
$myurl->param('id', $secret);

$PAGE->set_url($myurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('frontpage');
$PAGE->set_heading($title);
$PAGE->set_title($title);

/** @var \local_invitation\output\renderer $output */
$output = $PAGE->get_renderer('local_invitation');

$customdata  = ['invitation' => $invitation];
$confirmform = new \local_invitation\form\confirmation(null, $customdata);

if ($confirmform->is_cancelled()) {
    redirect(new \moodle_url('/'));
}

if ($confirmdata = $confirmform->get_data()) {
    // Create an account, enrol it to the course and log in the user.

    if (!$newuser = util::create_login_and_enrol($invitation, $confirmdata)) {
        throw new \moodle_exception('error_could_not_create_and_enrol', 'local_invitation');
    }

    $welcomenote = new \local_invitation\output\component\welcome_note($newuser);
    $urlparams   = [
        'id'   => $invitation->courseid,
        'lang' => $USER->lang,
    ];
    redirect(
        new \moodle_url('/course/view.php', $urlparams),
        $output->render($welcomenote),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$formwidget = new \local_invitation\output\component\form($confirmform, $title, true);
$infooutput = '';
if (isloggedin()) {
    $title      = get_string('note', 'local_invitation');
    $infomsg    = get_string('info_already_loggedin', 'local_invitation');
    $infowidget = new \local_invitation\output\component\infobox($title, $infomsg);
    $infooutput = $output->render($infowidget);
}

echo $output->header();
echo $infooutput;
echo $output->render($formwidget);
echo $output->footer();
