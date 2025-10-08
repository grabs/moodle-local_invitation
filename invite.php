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
 * Create an invitation.
 * @package    local_invitation
 * @author     Andreas Grabs <info@grabs-edv.de>
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_invitation\helper\util;

require_once(__DIR__ . '/../../config.php');

global $DB, $PAGE, $FULLME;

util::require_active();

$courseid = required_param('courseid', PARAM_INT); // Id of the related course.
$id       = optional_param('id', 0, PARAM_INT); // Id of the invitation record.

// If $id is set, we have to get the course from the invitation.
// This prevents us from accepting an invitation id from a different course!
if ($id) {
    $invitation = $DB->get_record('local_invitation', ['id' => $id], '*', MUST_EXIST);
    if ((int) $invitation->courseid !== $courseid) {
        throw new moodle_exception('Wrong id');
    }
}
$course = get_course($courseid);

$context = context_course::instance($course->id);

require_login($courseid);
util::require_can_use_invitation($context);

$title = get_string('invitation', 'local_invitation');

$myurl = new moodle_url($FULLME);
$myurl->remove_all_params();
$myurl->param('courseid', $courseid);
$courseurl = new moodle_url('/course/view.php', ['id' => $courseid]);

$PAGE->set_url($myurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');
$PAGE->set_heading($course->fullname);
$PAGE->set_title($title);

$coursename = empty($CFG->navshowfullcoursenames) ?
    format_string($course->shortname, true, ['context' => $context]) :
    format_string($course->fullname, true, ['context' => $context]);

$PAGE->navbar->ignore_active();
$PAGE->navbar->add($coursename, $courseurl);
$PAGE->navbar->add($title);

/** @var local_invitation\output\renderer $output */
$output = $PAGE->get_renderer('local_invitation');

$invitations           = $DB->get_records('local_invitation', ['courseid' => $courseid], 'timestart ASC');
$invitationinfowidgets = [];
// Customdata for all forms.
$customdata      = ['courseid' => $course->id];
$defaultautoopen = false;
if (count($invitations) == 1) {
    $defaultautoopen = true;
}

if (!util::has_max_invitations_reached($courseid)) {
    // Operations for new invitation.
    $inviteform = new local_invitation\form\edit(null, $customdata);

    if ($inviteform->is_cancelled()) {
        redirect(new moodle_url($myurl, ['courseid' => $courseid, 'id' => $id]));
    }

    $inviteopen = false;
    if (empty($id) && $inviteform->is_submitted()) {
        if ($invitedata = $inviteform->get_data()) {
            // Create the new invitation.
            if (!$newid = util::create_invitation($invitedata)) {
                throw new moodle_exception('could not create invitation');
            }
            // Redirect to me to prevent a accidentally reload.
            redirect(
                new moodle_url($myurl, ['courseid' => $courseid, 'id' => $newid]),
                get_string('invitation_created', 'local_invitation'),
                null,
                core\output\notification::NOTIFY_SUCCESS
            );
        } else {
            $inviteopen = true;
        }
    }
    $inviteformbox = new local_invitation\output\component\simple_modal_form(
        $inviteform,
        get_string('invite_participants', 'local_invitation'),
        get_string('new_invitation', 'local_invitation'),
        'fa-plus-circle fa-lg',
        $inviteopen
    );
}

// Operations for show and update existing invitations.
foreach ($invitations as $invitation) {
    $infoautoopen     = $defaultautoopen;
    $formautoopen     = false;
    $customdata['id'] = $invitation->id; // Append the id to the custom data.
    $editform         = new local_invitation\form\edit(null, $customdata);
    $deleteform       = new local_invitation\form\delete(null, $customdata);
    $editform->set_data($invitation);

    if ($editform->is_cancelled()) {
        redirect(new moodle_url($myurl, ['courseid' => $courseid, 'id' => $invitation->id]));
    }

    if ((int) $invitation->id === $id) {
        if ($editform->is_submitted()) { // Check submission only, if submitted!
            if ($invitedata = $editform->get_data()) {
                if (!util::update_invitation($invitation, $invitedata)) {
                    throw new moodle_exception('could not update invitation');
                }
                // Redirect to the invitation page.
                redirect(
                    new moodle_url($myurl, ['courseid' => $courseid, 'id' => $id]),
                    get_string('invitation_updated', 'local_invitation'),
                    null,
                    core\output\notification::NOTIFY_SUCCESS
                );
            } else {
                $formautoopen = true;
            }
        }

        if ($deleteform->is_submitted()) {
            if ($deletedata = $deleteform->get_data()) {
                if (!util::delete_invitation($deletedata->id)) {
                    throw new moodle_exception('could not delete invitation');
                }
                // Redirect to the invitation page.
                redirect(
                    $myurl,
                    get_string('invitation_deleted', 'local_invitation'),
                    null,
                    core\output\notification::NOTIFY_SUCCESS
                );
            }
        }

        $infoautoopen = true;
    }
    $invitationinfowidget    = new local_invitation\output\component\invitation_info(
        $invitation,
        $editform,
        $deleteform,
        $infoautoopen,
        $formautoopen
    );
    $invitationinfowidgets[] = $invitationinfowidget;
}

$widget = new local_invitation\output\component\invitation_list($invitationinfowidgets);

echo $output->header();
echo $output->heading($title);
echo $output->render(new local_invitation\output\component\possible_info($courseid));
if (!util::has_max_invitations_reached($courseid)) {
    echo $output->render($inviteformbox);
}
echo $output->render($widget);
echo $output->footer();
