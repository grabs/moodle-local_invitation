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
 * Renderable and templatable component for the infobox.
 *
 * @package    local_invitation
 * @author     Andreas Grabs <info@grabs-edv.de>
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class infobox extends base {
    /**
     * Constructor.
     *
     * @param string $title
     * @param string $text
     * @param string $type The style of the infobox. Can be 'warning', 'info', 'success', 'danger'.
     */
    public function __construct($title, $text, $type = 'info') {
        parent::__construct();
        $this->data['title'] = $title;
        $this->data['text']  = $text;
        if ($type == 'error') {
            $type = 'danger';
        }
        $this->data['type']  = $type;
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
