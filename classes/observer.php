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

namespace local_invitation;
use local_invitation\helper\date_time as datetime;
use local_invitation\globals as gl;

defined('MOODLE_INTERNAL') || die();

/**
 * Observer class.
 *
 * @package    local_invitation
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {

    /**
     * A course has been deleted.
     *
     * @param \core\event\course_deleted $event The event.
     * @return void
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        $DB = gl::db();

        $courseid = $event->courseid;

        $DB->delete_records('local_invitation', array('courseid' => $courseid));
    }
}
