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
class invitation_list extends base {
    /** @var invitation_info[] The invitation_info widgets to be rendered before passing to the template */
    protected $invitationinfowidgets;

    /**
     * Constructor for the invitation_list class.
     *
     * Initializes the invitation_list object with the provided invite widgets.
     *
     * @param invitation_info[] $invitationinfowidgets An array of invitation_info widget objects to be displayed in the list.
     */
    public function __construct($invitationinfowidgets) {
        global $DB;
        parent::__construct();

        $this->invitationinfowidgets = $invitationinfowidgets;
    }

    /**
     * Data for usage in mustache.
     *
     * @param  \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        $renderedwidgets = [];

        foreach ($this->invitationinfowidgets as $invitationinfowidget) {
            $renderedwidgets[] = $output->render($invitationinfowidget);
        }
        $this->data['hasinvitations'] = count($renderedwidgets);
        $this->data['invitations'] = $renderedwidgets;

        return $this->data;
    }
}
