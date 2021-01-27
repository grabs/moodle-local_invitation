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

// We do not have a login check in this file because the login is actually done here.
// So we have to ignore the codingstyle for the config.php inclusion which normally requires a login check.
// @codingStandardsIgnoreLine
require_once(dirname(__FILE__) . '/../../config.php');

util::require_active();

// Because it is an enrolment we use the system context.
$context = context_system::instance();

$title = get_string('error_invalid_invitation', 'local_invitation');

$myurl = new \moodle_url($FULLME);
$myurl->remove_all_params();

/** @var \moodle_page $PAGE */
$PAGE->set_url($myurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('frontpage');
$PAGE->set_heading($title);
$PAGE->set_title($title);

/** @var \local_invitation\output\renderer $output */
$output = $PAGE->get_renderer('local_invitation');

if (isloggedin()) {
    redirect(new \moodle_url('/'));
}

echo $output->header();
echo $output->heading($title);
$btn = new single_button(new \moodle_url('/'), get_string('continue'), 'get', true);
echo $output->render($btn);
echo $output->footer();
