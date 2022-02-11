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
 * The invitation form.
 *
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class invite extends base {

    private $myconfig;

    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        global $CFG;

        $this->myconfig = get_config('local_invitation');
        if (empty($this->myconfig->userrole)) {
            throw new \moodle_exception('error_userrole_not_defined', 'local_invitation');
        }

        $mform = $this->_form;

        $customdata = (object) $this->_customdata;
        if (empty($customdata->courseid)) {
            throw new \moodle_exception('Missing courseid in customdata');
        }

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setConstant('courseid', $customdata->courseid);

        $mform->addElement('hidden', 'userrole');
        $mform->setType('userrole', PARAM_INT);
        $mform->setConstant('userrole', $this->myconfig->userrole);

        $options = self::get_maxusers_options($this->myconfig->maxusers);
        $mform->addElement('select', 'maxusers', get_string('max_users', 'local_invitation'), $options);

        $timestart = datetime::floor_to_day(time());
        $timeend = $timestart + datetime::DAY - datetime::MINUTE; // This means 23:59.
        $timeoptions = array('startyear' => datetime::get_year(time()), 'stopyear' => datetime::get_year(time()) + 1);
        $mform->addElement(
            'date_time_selector',
            'timestart',
            get_string('available_from', 'local_invitation'),
            $timeoptions
        );
        $mform->setDefault('timestart', $timestart);
        $mform->addElement(
            'date_time_selector',
            'timeend',
            get_string('available_to', 'local_invitation'),
            $timeoptions
        );
        $mform->setDefault('timeend', $timeend);

        $this->add_action_buttons();

    }

    /**
     * The mform validation method
     *
     * @global \moodle_database $DB
     * @param \stdClass $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {

        $errors = parent::validation($data, $files);

        $data = (object) $data;

        $today = datetime::floor_to_day(time());

        if ($data->timeend < $data->timestart) {
            $errors['timestart'] = get_string('error_timeend_can_not_be_before_timestart', 'local_invitation');
            $errors['timeend'] = get_string('error_timeend_can_not_be_before_timestart', 'local_invitation');
        }

        if ($data->timeend < $today) {
            $errors['timeend'] = get_string('error_timeend_can_not_be_in_past', 'local_invitation');
        }
        return $errors;
    }
}
