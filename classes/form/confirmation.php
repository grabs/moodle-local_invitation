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

namespace local_invitation\form;
use local_invitation\helper\date_time as datetime;
use local_invitation\globals as gl;

defined('MOODLE_INTERNAL') || die;

class confirmation extends base {

    private $myconfig;

    function definition() {
        global $CFG;

        $this->myconfig = get_config('local_invitation');
        if (empty($this->myconfig->userrole)) {
            throw new \moodle_exception('userrole not defined in config');
        }

        $mform = $this->_form;
        $customdata = (object) $this->_customdata;
        if (empty($customdata->invitation)) {
            throw new \moodle_exception('Invalid or missing invitation');
        }
        $invitation = $customdata->invitation;

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setConstant('courseid', $invitation->courseid);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_TEXT);
        $mform->setConstant('id', $invitation->secret);

        $mform->addElement('text', 'firstname', get_string('firstname'));
        $mform->setType('firstname', PARAM_TEXT);
        $mform->addRule('firstname', null, 'required', null, 'client');

        $mform->addElement('text', 'lastname', get_string('lastname'));
        $mform->setType('lastname', PARAM_TEXT);
        $mform->addRule('lastname', null, 'required', null, 'client');

        $submitlabel = get_string('join', 'local_invitation');
        $this->add_action_buttons(true, $submitlabel);

    }

}
