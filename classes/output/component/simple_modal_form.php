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
 * Renderable and templatable component for the edit form.
 *
 * @package    local_invitation
 * @author     Andreas Grabs <info@grabs-edv.de>
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class simple_modal_form extends base {
    /** @var \local_invitation\form\base */
    private $form;

    /**
     * Constructor for the simple_modal_form class.
     *
     * This function initializes a new instance of the simple_modal_form class,
     * setting up the form and various display properties for a modal dialog.
     *
     * @param \local_invitation\form\base $form The form object to be displayed in the modal.
     * @param string $title The title of the modal dialog.
     * @param string $linktitle The text to be displayed on the link that opens the modal. Default is an empty string.
     * @param string $icon The icon to be displayed alongside the modal link. Default is 'fa-cog'.
     * @param bool $autoopen Whether the modal should automatically open when the page loads. Default is false.
     */
    public function __construct($form, $title, $linktitle = '', $icon = 'fa-cog', $autoopen = false) {
        global $DB;
        parent::__construct();

        $this->form = $form;
        $this->data['autoopen'] = $autoopen;
        $this->data['icon'] = $icon;
        $this->data['title'] = $title;
        $this->data['linktitle'] = $linktitle;
    }

    /**
     * Prepares and exports data for use in a mustache template.
     *
     * This function processes the form content and adds it to the data array
     * for rendering in a template. It's typically used to prepare data for
     * display in the user interface.
     *
     * @param \renderer_base $output The renderer base object used for output generation.
     * @return array The prepared data array containing form content and other information for template rendering.
     */
    public function export_for_template(\renderer_base $output) {
        $this->data['formcontent'] = $this->form->export_for_template($output);

        return $this->data;
    }
}
