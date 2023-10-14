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

namespace local_invitation\form;

/**
 * Delete form.
 *
 * @package    local_invitation
 * @author     Andreas Grabs <info@grabs-edv.de>
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete extends base {
    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $customdata = (object) $this->_customdata;
        if (empty($customdata->courseid)) {
            throw new \moodle_exception('Missing courseid in customdata');
        }

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setConstant('id', $customdata->id);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setConstant('courseid', $customdata->courseid);

        $mform->addElement('html', '<div>' . get_string('delete_confirmation', 'local_invitation') . '</div>');

        $this->add_action_buttons(true, get_string('delete'));
    }
}
