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

namespace local_invitation\output\component;
use local_invitation\helper\date_time as datetime;
use local_invitation\globals as gl;

defined('MOODLE_INTERNAL') || die();

class invitation_info extends base {
    private $editwidget;

    public function __construct(\stdClass $invitation, $editform, $autoopen) {
        $DB = gl::db();
        parent::__construct();

        $usedslots = $DB->count_records('local_invitation_users', array('invitationid' => $invitation->id));

        $this->editwidget = new edit_form_box($editform, $autoopen);

        $urlparams = array(
            'courseid' => $invitation->courseid,
            'id' => $invitation->secret,
        );
        $dateformat = get_string('strftimedatetimeshort');
        $slots = intval($invitation->maxusers) - $usedslots;
        $this->data['title'] = get_string('current_invitation', 'local_invitation');
        $this->data['url'] = new \moodle_url('/local/invitation/join.php', $urlparams);
        $this->data['timestart'] = userdate($invitation->timestart, $dateformat, 99, false);
        $this->data['timeend'] = userdate($invitation->timeend, $dateformat, 99, false);
        $this->data['slots'] = $slots;
        $this->data['freeslots'] = $slots > 0;
        $this->data['note'] = get_string('current_invitation_note', 'local_invitation');
    }

    /**
     * Data for usage in mustache
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        $this->data['editformbox'] = $output->render($this->editwidget);
        return $this->data;
    }
}
