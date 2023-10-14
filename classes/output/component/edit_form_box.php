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

use local_invitation\globals as gl;

/**
 * Renderable and templatable component for the edit form.
 *
 * @package    local_invitation
 * @author     Andreas Grabs <info@grabs-edv.de>
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_form_box extends base {
    /** @var \local_invitation\form\base */
    private $editform;

    /**
     * Constructor.
     *
     * @param \local_invitation\form\base $editform
     * @param bool                        $autoopen
     */
    public function __construct($editform, $autoopen) {
        $DB = gl::db();
        parent::__construct();

        $this->editform          = $editform;
        $this->data['autoopen']  = $autoopen;
        $this->data['linktitle'] = '<i class="fa fa-cog fa-lg"></i>';
        $this->data['title']     = get_string('edit_invitation', 'local_invitation');
    }

    /**
     * Data for usage in mustache.
     *
     * @param  \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        $this->data['formcontent'] = $this->editform->export_for_template($output);

        return $this->data;
    }
}
