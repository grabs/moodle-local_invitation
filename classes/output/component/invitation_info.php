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
class invitation_info extends base {
    /** @var simple_modal_form */
    private $editwidget;
    /** @var simple_modal_form */
    private $deletewidget;

    /**
     * Constructor for the invitation_info class.
     *
     * This function initializes an invitation_info object with the provided invitation details,
     * creates edit and delete widgets, and prepares various data for display.
     *
     * @param \stdClass $invitation   The invitation object containing details like id, courseid, title, etc.
     * @param mixed     $editform     The form object for editing the invitation.
     * @param mixed     $deleteform   The form object for deleting the invitation.
     * @param bool      $infoautoopen Whether the info should be automatically opened.
     * @param bool      $formautoopen Whether the edit form should be automatically opened.
     *
     * @return void
     */
    public function __construct(\stdClass $invitation, $editform, $deleteform, $infoautoopen, $formautoopen) {
        global $DB;
        parent::__construct();

        // Count the number of users who have used this invitation.
        $usedslots = $DB->count_records('local_invitation_users', ['invitationid' => $invitation->id]);

        // Create a modal form widget for editing the invitation.
        $this->editwidget   = new simple_modal_form(
            $editform,
            get_string('edit_invitation', 'local_invitation'),
            '',
            'fa-pencil fa-lg',
            $formautoopen
        );

        // Create a modal form widget for deleting the invitation.
        $this->deletewidget = new simple_modal_form(
            $deleteform,
            get_string('delete_invitation', 'local_invitation'),
            '',
            'fa-trash fa-lg text-danger'
        );

        // Prepare URL parameters for the invitation link.
        $urlparams = [
            'courseid' => $invitation->courseid,
            'id'       => $invitation->secret,
        ];
        // Create URLs for the course and invitation.
        $courseurl     = new \moodle_url('/course/view.php', ['id' => $invitation->courseid]);
        $invitationurl = new \moodle_url('/local/invitation/join.php', $urlparams);

        // Set up date format and prepare data for display.
        $dateformat                     = get_string('strftimedatetimeshort');
        $this->data['title']            = $invitation->title ?? get_string('invitation', 'local_invitation');
        $this->data['url']              = $invitationurl;
        $this->data['timestart']        = userdate($invitation->timestart, $dateformat, 99, false);
        $this->data['timestartwarning'] = $invitation->timestart > time();
        $this->data['timeend']          = userdate($invitation->timeend, $dateformat, 99, false);
        $this->data['timeendwarning']   = $invitation->timeend < time();
        $this->data['courseurl']        = $courseurl;

        // Set up slot information.
        $this->data['usedslots'] = $usedslots;
        if ($invitation->maxusers != 0) {
            // Calculate remaining slots if there's a limit.
            $slots                   = (int) $invitation->maxusers - $usedslots;
            $this->data['slots']     = $slots;
            $this->data['freeslots'] = $slots > 0;
        } else {
            // Set slots to unlimited if there's no maximum.
            $this->data['slots']     = get_string('unlimited');
            $this->data['freeslots'] = true;
        }

        // Generate QR code for the invitation URL.
        $qrcode                          = new \core_qrcode($invitationurl->out(false));
        $this->data['qrcodetitle']       = get_string('qrcode', 'local_invitation');
        $this->data['qrcodebuttontitle'] = get_string('showqrcode', 'local_invitation');
        $this->data['qrcodeimg']         = 'data:image/png;base64,' . base64_encode((string) $qrcode->getBarcodePngData(5, 5));

        // Set additional information.
        $this->data['note'] = get_string('current_invitation_note', 'local_invitation');
        $this->data['autoopen'] = $infoautoopen;
    }

    /**
     * Data for usage in mustache.
     *
     * @param  \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        $this->data['editformbox']   = $output->render($this->editwidget);
        $this->data['deleteformbox'] = $output->render($this->deletewidget);

        return $this->data;
    }
}
