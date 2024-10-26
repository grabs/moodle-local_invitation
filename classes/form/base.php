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
use local_invitation\helper\util;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');

/**
 * Base form class.
 *
 * @package    local_invitation
 * @author     Andreas Grabs <info@grabs-edv.de>
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base extends \moodleform implements \renderable, \templatable {

    /**
     * Adds a usegroup element to the form.
     *
     * Adds a checkbox to the form to switch between using the group
     * or not. If the checkbox is checked, an autocomplete element
     * is added to select the group.
     *
     * @param \MoodleQuickForm $mform The form to add the element to.
     * @param int $courseid The id of the course the group belongs to.
     */
    protected function add_usegroup_element(\MoodleQuickForm $mform, int $courseid) {
        $mform->addElement('checkbox', 'usegroup', get_string('usegroup', 'local_invitation'));
        $attributes = [
            'ajax' => 'local_invitation/form_group_selector',
            'multiple' => false,
            'courseid' => $courseid,
            'noselectionstring' => get_string('no_group_defined', 'local_invitation'),
            'showsuggestions' => true,
            'placeholder' => get_string('search_or_create_group', 'local_invitation'),
        ];
        $mform->addElement('autocomplete', 'groupid', get_string('group'), [], $attributes);
        $mform->setType('groupid', PARAM_TEXT);
        $mform->hideIf('groupid', 'usegroup');
    }

    /**
     * Validate usegroup
     *
     * @param \stdClass $data
     * @param array $errors
     * @return array
     */
    protected function validate_usegroup(\stdClass $data, array $errors) {
        if (!empty($data->usegroup)) {
            if (empty($data->groupid)) {
                $errors['usegroup'] = get_string('no_group_defined', 'local_invitation');
            } else {
                // If we got a numeric groupid, check that it exists in the course.
                if (is_number($data->groupid)) {
                    if (!util::group_exists_in_course($data->groupid, $data->courseid)) {
                        $errors['groupid'] = get_string('group_not_found', 'local_invitation');
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Prepare usegroup data
     *
     * @param \stdClass $data
     * @return \stdClass
     */
    protected function prepare_usegroup_data(\stdClass $data) {
        if (!empty($data)) {
            if (!empty($data->usegroup)) {
                if (!is_number($data->groupid)) {
                    $data->groupname = preg_replace('#^NEW_#', '', $data->groupid);
                    $data->groupid = -1; // Minus 1 means "create the group from the given groupname".
                }
            } else {
                $data->groupid = 0;
            }
        }

        return $data;
    }

    /**
     * Get the form output as html.
     *
     * @param  \renderer_base $output
     * @return string
     */
    public function export_for_template(\renderer_base $output) {
        ob_start();
        $this->display();
        $data = ob_get_contents();
        ob_end_clean();

        return $data;
    }

    /**
     * Get an option list array to use in select boxes.
     *
     * @param  int   $maxusers
     * @return array
     */
    public static function get_maxusers_options($maxusers) {
        if ($maxusers == 0) {
            // This means it is unlimited.
            $unlimited   = [0 => get_string('unlimited')];
            $optionslow  = array_combine(range(5, 50, 5), range(5, 50, 5));
            $optionsmid  = array_combine(range(60, 150, 10), range(60, 150, 10));
            $optionshigh = array_combine(range(200, 1000, 50), range(200, 1000, 50));
        } else if ($maxusers < 60) {
            $unlimited  = $optionsmid = $optionshigh = [];
            $optionslow = array_combine(range(5, $maxusers, 5), range(5, $maxusers, 5));
        } else if ($maxusers < 200) {
            $unlimited  = $optionshigh = [];
            $optionslow = array_combine(range(5, 50, 5), range(5, 50, 5));
            $optionsmid = array_combine(range(60, $maxusers, 10), range(60, $maxusers, 10));
        } else {
            $unlimited   = [];
            $optionslow  = array_combine(range(5, 50, 5), range(5, 50, 5));
            $optionsmid  = array_combine(range(60, 150, 10), range(60, 150, 10));
            $optionshigh = array_combine(range(200, $maxusers, 50), range(200, $maxusers, 50));
        }

        $options = $optionslow + $optionsmid + $optionshigh + $unlimited;

        return $options;
    }

    /**
     * Get an option array for expiration select box.
     *
     * @return array
     */
    public static function get_expiration_options() {
        $optionslow  = array_combine(range(1, 49), range(1, 49));
        $optionsmid  = array_combine(range(5, 50, 5), range(5, 50, 5));
        $optionshigh = array_combine(range(60, 150, 10), range(60, 150, 10));

        $options = $optionslow + $optionsmid + $optionshigh;

        return $options;
    }
}
