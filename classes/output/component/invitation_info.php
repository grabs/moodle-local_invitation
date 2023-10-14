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
class invitation_info extends base {
    /** @var edit_form_box */
    private $editwidget;
    /** @var delete_form_box */
    private $deletewidget;

    /**
     * Constructor.
     *
     * @param \stdClass                   $invitation
     * @param \local_invitation\form\base $editform
     * @param \local_invitation\form\base $deleteform
     * @param bool                        $autoopen
     */
    public function __construct(\stdClass $invitation, $editform, $deleteform, $autoopen) {
        $DB = gl::db();
        parent::__construct();

        $usedslots = $DB->count_records('local_invitation_users', ['invitationid' => $invitation->id]);

        $this->editwidget   = new edit_form_box($editform, $autoopen);
        $this->deletewidget = new delete_form_box($deleteform);

        $urlparams = [
            'courseid' => $invitation->courseid,
            'id'       => $invitation->secret,
        ];
        $courseurl     = new \moodle_url('/course/view.php', ['id' => $invitation->courseid]);
        $invitationurl = new \moodle_url('/local/invitation/join.php', $urlparams);

        $dateformat                     = get_string('strftimedatetimeshort');
        $this->data['title']            = get_string('current_invitation', 'local_invitation');
        $this->data['url']              = $invitationurl;
        $this->data['timestart']        = userdate($invitation->timestart, $dateformat, 99, false);
        $this->data['timestartwarning'] = $invitation->timestart > time();
        $this->data['timeend']          = userdate($invitation->timeend, $dateformat, 99, false);
        $this->data['timeendwarning']   = $invitation->timeend < time();
        $this->data['courseurl']        = $courseurl;

        $this->data['usedslots'] = $usedslots;
        if ($invitation->maxusers != 0) {
            $slots                   = (int) $invitation->maxusers - $usedslots;
            $this->data['slots']     = $slots;
            $this->data['freeslots'] = $slots > 0;
        } else {
            $this->data['slots']     = get_string('unlimited');
            $this->data['freeslots'] = true;
        }

        $qrcode                          = new \core_qrcode($invitationurl->out(false));
        $this->data['qrcodetitle']       = get_string('qrcode', 'local_invitation');
        $this->data['qrcodebuttontitle'] = get_string('showqrcode', 'local_invitation');
        $this->data['qrcodeimg']         = 'data:image/png;base64,' . base64_encode((string) $qrcode->getBarcodePngData(5, 5));

        $this->data['note'] = get_string('current_invitation_note', 'local_invitation');
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
