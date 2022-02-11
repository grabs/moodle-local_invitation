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
use local_invitation\helper\util;
use local_invitation\globals as gl;

/**
 * Confirmation form.
 *
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class confirmation extends base {

    private $myconfig;

    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        $CFG = gl::cfg();
        $OUTPUT = gl::output();
        $mycfg = gl::mycfg();

        $this->myconfig = get_config('local_invitation');
        if (empty($this->myconfig->userrole)) {
            throw new \moodle_exception('error_userrole_not_defined', 'local_invitation');
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

        // Define the firstname field.
        // If we use a single name field we label it "name" otherwise "firstname".
        // While using a single name field the lastname is set automatically with "guestuser".
        if (!empty($mycfg->singlenamefield)) {
            $firstnamelabel = get_string('name');
        } else {
            $firstnamelabel = get_string('firstname');
        }
        $mform->addElement('text', 'firstname', $firstnamelabel);
        $mform->setType('firstname', PARAM_TEXT);
        $mform->addRule('firstname', null, 'required', null, 'client');

        // Define the lastname field only if we don't use a single name field.
        if (empty($mycfg->singlenamefield)) {
            $mform->addElement('text', 'lastname', get_string('lastname'));
            $mform->setType('lastname', PARAM_TEXT);
            $mform->addRule('lastname', null, 'required', null, 'client');
        }

        if (!empty($mycfg->nameinfo)) { // Should there be an info text to the name field?
            $mform->addElement('static', 'static1', '', $mycfg->nameinfo);
            $mform->addElement('html', '<hr>');
        }

        if ($consent = util::get_consent()) {
            $consent = format_text($consent);
            $consenttitle = get_string('consent_title', 'local_invitation');
            $mform->addElement('checkbox', 'consent', $consenttitle, $consent);
            $mform->addRule('consent', get_string('required'), 'required', null, 'client');
        }

        $submitlabel = get_string('join', 'local_invitation');
        $this->add_action_buttons(true, $submitlabel);

    }

    /**
     * Get the form data
     *
     * @return array|object
     */
    public function get_data() {
        $mycfg = gl::mycfg();

        if (!$data = parent::get_data()) {
            return $data;
        }

        if (!empty($mycfg->singlenamefield)) {
            // Add the string "guestuser_suffix" as lastname.
            $data->lastname = get_string('guestuser_suffix', 'local_invitation');
        }

        return $data;
    }
}
