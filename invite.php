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

require_once(dirname(__FILE__) . '/../../config.php');

util::require_active();

$courseid = required_param('courseid', PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);

$context = context_course::instance($courseid);
$course = get_course($courseid);
$autoopen = false;

$DB = gl::db();

require_login($courseid);
require_capability('local/invitation:manage', $context);

$title = get_string('invite_participants', 'local_invitation');

$myurl = new \moodle_url($FULLME);
$myurl->remove_all_params();
$myurl->param('courseid', $courseid);
$courseurl = new \moodle_url('/course/view.php', array('id' => $courseid));

/** @var \moodle_page $PAGE */
$PAGE->set_url($myurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');
$PAGE->set_heading($course->fullname);
$PAGE->set_title($title);

$coursesurl = new \moodle_url('/course/index.php');
$coursename = empty($CFG->navshowfullcoursenames) ?
    format_string($course->shortname, true, array('context' => $context)) :
    format_string($course->fullname, true, array('context' => $context));

$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('courses'), $coursesurl);
$PAGE->navbar->add(s($coursename), $courseurl);
$PAGE->navbar->add($title, $myurl);

/** @var \local_invitation\output\renderer $output */
$output = $PAGE->get_renderer('local_invitation');

$invitationinfo = '';
$invitationnote = $output->render_from_template('local_invitation/invitation_note', array('note' => util::get_invitation_note()));
// Common custom data for both forms (invite and update).
$customdata = array(
    'courseid' => $courseid,
);

// If there is an invitation we create an info box and a edit form.
if ($invitation = $DB->get_record('local_invitation', array('courseid' => $courseid))) {

    // The editopen is used on errors to open the modalbox with the editform after an error.
    $editopen = false;
    $customdata['id'] = $invitation->id; // Append the id to the custom data.

    $editform = new \local_invitation\form\update(null, $customdata);
    $deleteform = new \local_invitation\form\delete(null, $customdata);

    $editform->set_data($invitation);
    if ($editform->is_cancelled()) {
        redirect($myurl);
    }
    if ($deleteform->is_cancelled()) {
        redirect($myurl);
    }

    // We need to check whether or not the form is submitted to be aware of some errors in the form.
    // If there is an error we want the modal box auto open.
    if ($editform->is_submitted()) {
        if ($invitedata = $editform->get_data()) {
            if (!util::update_invitation($invitation, $invitedata)) {
                throw new \moodle_exception('could not update invitation');
            }
            // Redirect to the invitation page.
            redirect(
                $myurl,
                get_string('invitation_updated', 'local_invitation'),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
        } else {
            $editopen = true;
        }
    }

    if ($deleteform->is_submitted()) {
        if ($deletedata = $deleteform->get_data()) {
            if (!util::delete_invitation($deletedata->id)) {
                throw new \moodle_exception('could not delete invitation');
            }
            // Redirect to the invitation page.
            redirect(
                $myurl,
                get_string('invitation_deleted', 'local_invitation'),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
        }
    }

    $invitewidget = new \local_invitation\output\component\invitation_info($invitation, $editform, $deleteform, $editopen);
    $invitationinfo = $output->render($invitewidget);

    $formwidget = '';
} else {
    $autoopen = true;
    // This is the form to create a new invitation.
    $inviteform = new \local_invitation\form\invite(null, $customdata);

    if ($inviteform->is_cancelled()) {
        redirect(new \moodle_url('/course/view.php', array('id' => $courseid)));
    }

    // We need to check whether or not the form is submitted to be aware of some errors in the form.
    // If there is an error we want the collapse auto open.
    if ($inviteform->is_submitted()) {
        if ($invitedata = $inviteform->get_data()) {
            // Create the new invitation.
            if (!util::create_invitation($invitedata)) {
                throw new \moodle_exception('could not create invitation');
            }
            // Redirect to me to prevent a accidentally reload.
            redirect(
                $myurl,
                get_string('invitation_created', 'local_invitation'),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
        } else {
            $autoopen = true;
        }
    }

    $formwidget = new \local_invitation\output\component\form($inviteform, $title, $autoopen, $courseurl);
    $formwidget = $output->render($formwidget);
}

echo $output->header();
echo $invitationnote;
echo $invitationinfo;
echo $formwidget;
echo $output->footer();
