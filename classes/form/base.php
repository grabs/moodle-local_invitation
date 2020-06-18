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
}
