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

require_once($CFG->libdir.'/formslib.php');

/**
 * Base form class.
 *
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base extends \moodleform implements \renderable, \templatable {

    /**
     * Get the form output as html.
     *
     * @param \renderer_base $output
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
     * @param int $maxusers
     * @return array
     */
    public static function get_maxusers_options($maxusers) {

        if ($maxusers == 0) {
            // This means it is unlimited.
            $unlimited = array(0 => get_string('unlimited'));
            $optionslow = array_combine(range(5, 50, 5), range(5, 50, 5));
            $optionsmid = array_combine(range(60, 150, 10), range(60, 150, 10));
            $optionshigh = array_combine(range(200, 1000, 50), range(200, 1000, 50));
        } else if ($maxusers < 60) {
            $unlimited = $optionsmid = $optionshigh = array();
            $optionslow = array_combine(range(5, $maxusers, 5), range(5, $maxusers, 5));
        } else if ($maxusers < 200) {
            $unlimited = $optionshigh = array();
            $optionslow = array_combine(range(5, 50, 5), range(5, 50, 5));
            $optionsmid = array_combine(range(60, $maxusers, 10), range(60, $maxusers, 10));
        } else {
            $unlimited = array();
            $optionslow = array_combine(range(5, 50, 5), range(5, 50, 5));
            $optionsmid = array_combine(range(60, 150, 10), range(60, 150, 10));
            $optionshigh = array_combine(range(200, $maxusers, 50), range(200, $maxusers, 50));
        }

        $options = $optionslow + $optionsmid + $optionshigh + $unlimited;
        return $options;
    }

    /**
     * Get an option array for expiration select box
     *
     * @return array
     */
    public static function get_expiration_options() {

        $optionslow = array_combine(range(1, 49), range(1, 49));
        $optionsmid = array_combine(range(5, 50, 5), range(5, 50, 5));
        $optionshigh = array_combine(range(60, 150, 10), range(60, 150, 10));

        $options = $optionslow + $optionsmid + $optionshigh;
        return $options;
    }
}
