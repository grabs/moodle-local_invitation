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

$context = context_course::instance($courseid);
$course = get_course($courseid);

$DB = gl::db();

require_login($courseid);
require_capability('local/invitation:manage', $context);

$title = get_string('invite_participants', 'local_invitation');

$myurl = new \moodle_url($FULLME);
$myurl->remove_all_params();
$myurl->param('courseid', $courseid);

/** @var \moodle_page $PAGE */
$PAGE->set_url($myurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$PAGE->set_heading($course->fullname);
$PAGE->set_title($title);

/** @var \local_invitation\output\renderer $output */
$output = $PAGE->get_renderer('local_invitation');

$customdata = array(
    'courseid' => $courseid,
);
$inviteform = new \local_invitation\form\invite(null, $customdata);

if ($inviteform->is_cancelled()) {
    redirect(new \moodle_url('/course/view.php', array('id' => $courseid)));
}

if ($invitedata = $inviteform->get_data()) {
    // First we check whether or not an invitation already exists.
    $DB->delete_records('local_invitation', array('courseid' => $courseid));
    $invitedata->timemodified = time();
    $invitedata->secret = util::generate_secret_for_inventation();
    $DB->insert_record('local_invitation', $invitedata);
    // Redirect to me to prevent a accidentally reload.
    redirect(
        $myurl,
        get_string('invitation_created', 'local_invitation'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$inviteout = '';
if ($invitation = $DB->get_record('local_invitation', array('courseid' => $courseid))) {
    $invitewidget = new \local_invitation\output\component\invitation_info($invitation);
    $inviteout = $output->render($invitewidget);
}
$formwidget = new \local_invitation\output\component\form($inviteform, $title);

echo $output->header();
// echo $output->heading($title);
// $inviteform->display();
echo $inviteout;
echo $output->render($formwidget);
echo $output->footer();
