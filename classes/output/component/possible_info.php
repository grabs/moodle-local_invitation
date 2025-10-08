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

namespace local_invitation\output\component;

/**
 * Renderable and templatable component to show a list of invitations.
 *
 * @package    local_invitation
 * @author     Andreas Grabs <info@grabs-edv.de>
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class possible_info extends base {
    /**
     * Constructor for the possible_info class.
     *
     * This function initializes the object with information about possible invitations
     * for a given course. It calculates the current number of invitations and compares
     * it with the maximum allowed invitations.
     *
     * @param int $courseid The ID of the course for which to retrieve invitation information.
     */
    public function __construct($courseid) {
        global $DB;
        parent::__construct();

        // Get the configuration settings for the local_invitation plugin.
        $mycfg = get_config('local_invitation');

        // Count the number of existing invitations for the given course.
        $count = $DB->count_records('local_invitation', ['courseid' => $courseid]);

        // Generate a string describing the current and maximum number of invitations.
        $possibleinfo = get_string(
            'possible_invitations_from_to',
            'local_invitation',
            (object) ['from' => $count, 'to' => $mycfg->maxinvitations]
        );

        // Store the generated string in the data array for use in the template.
        $this->data['possibleinfo'] = $possibleinfo;

        // Check if the maximum number of invitations has been reached or exceeded.
        $this->data['exceeded'] = ($count >= $mycfg->maxinvitations);
    }

    /**
     * Data for usage in mustache.
     *
     * @param  \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        return $this->data;
    }
}
