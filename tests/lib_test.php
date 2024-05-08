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
 * Unit tests for general local_invitation features.
 *
 * @package     local_invitation
 * @category    test
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_invitation;

use local_invitation\globals as gl;
use local_invitation\helper\date_time as datetime;
use local_invitation\helper\util;

/**
 * Unit tests for general invitation features.
 *
 * @package     local_invitation
 * @category    test
 * @author      Andreas Grabs <info@grabs-edv.de>
 * @copyright   2018 onwards Grabs EDV {@link https://www.grabs-edv.de}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class lib_test extends \advanced_testcase {
    /** @var array */
    private $examples;

    /**
     * Set up the test.
     *
     * @return void
     */
    protected function setUp(): void {
        $CFG = gl::cfg();

        $this->resetAfterTest();
        $this->setAdminUser();

        $examples       = file_get_contents($CFG->dirroot . '/local/invitation/tests/fixtures/sampeldata.json');
        $this->examples = json_decode($examples);
    }

    /**
     * Test rendering an invitation note.
     *
     * @covers \local_invitation\helper\util
     * @return void
     */
    public function test_render_invitation_note(): void {
        $PAGE = gl::page();

        /** @var \local_invitation\output\renderer $output */
        $output = $PAGE->get_renderer('local_invitation');
        $this->assertIsObject($output);

        $invitationnote = $output->render_from_template(
            'local_invitation/invitation_note',
            [
                'note' => util::get_invitation_note(),
            ]
        );
        $this->assertIsString($invitationnote);
    }

    /**
     * Test to create and delete invitations.
     *
     * @covers \local_invitation\helper\util
     * @return void
     */
    public function test_create_invitation(): void {
        $CFG   = gl::cfg();
        $DB    = gl::db();
        $mycfg = gl::mycfg();

        // Generate for each example a new course + invitation.
        foreach ($this->examples as $example) {
            $course = $this->getDataGenerator()->create_course();
            // Simulate the form data for creating a new invitation.
            $invitedata            = new \stdClass();
            $invitedata->courseid  = $course->id;
            $invitedata->maxusers  = $example->maxusers;
            $invitedata->userrole  = $mycfg->userrole;
            $invitedata->timestart = time() + datetime::DAY;
            $invitedata->timeend   = time() + 2 * datetime::DAY;

            $result = util::create_invitation($invitedata);
            $this->assertTrue((bool) $result);
        }
        // Compare the count of created invitations with the number of examples.
        $invitations = $DB->get_records('local_invitation');
        $this->assertEquals(count($this->examples), count($invitations));

        // Delete the invitations.
        foreach ($invitations as $invitation) {
            $result = util::delete_invitation($invitation->id);
            $this->assertTrue($result);
        }
    }

    /**
     * Test to use an invitation as user.
     *
     * @covers \local_invitation\helper\util
     * @return void
     */
    public function test_use_invitation(): void {
        $PAGE  = gl::page();
        $DB    = gl::db();
        $mycfg = gl::mycfg();

        $course = $this->getDataGenerator()->create_course();
        // Simulate the form data for creating a new invitation.
        $invitedata            = new \stdClass();
        $invitedata->courseid  = $course->id;
        $invitedata->maxusers  = $this->examples[0]->maxusers;
        $invitedata->userrole  = $mycfg->userrole;
        $invitedata->timestart = time();
        $invitedata->timeend   = time() + 2 * datetime::DAY;

        $result = util::create_invitation($invitedata);
        $this->assertTrue((bool) $result);

        // Lets get the the invitation-secret by using the courseid.
        $invitationsecret = $DB->get_field('local_invitation', 'secret', ['courseid' => $course->id]);
        $this->assertIsString($invitationsecret);

        // Now we get the invitation by using the secret.
        // This is also checking the time.
        $invitation = util::get_invitation_from_secret($invitationsecret, $course->id);
        $this->assertIsObject($invitation);

        // Simulate the confirm form.
        $confirmdata            = new \stdClass();
        $confirmdata->firstname = 'George';
        $confirmdata->lastname  = 'Meyer';
        $confirmdata->consent   = true;
        // Create and login the new user.
        $newuser = util::create_login_and_enrol($invitation, $confirmdata);
        $this->assertIsObject($newuser);

        /** @var \local_invitation\output\renderer $output */
        $output = $PAGE->get_renderer('local_invitation');
        $this->assertIsObject($output);

        // Generate the welcome note.
        $welcomenote = $output->render(new \local_invitation\output\component\welcome_note($newuser));
        $this->assertIsString($welcomenote);
    }

    /**
     * Test whether a given invitation is deleted when the related course is deleted.
     *
     * @covers \local_invitation\helper\util
     * @return void
     */
    public function test_course_deletion(): void {
        $DB    = gl::db();
        $mycfg = gl::mycfg();

        $course = $this->getDataGenerator()->create_course();
        // Simulate the form data for creating a new invitation.
        $invitedata            = new \stdClass();
        $invitedata->courseid  = $course->id;
        $invitedata->maxusers  = $this->examples[0]->maxusers;
        $invitedata->userrole  = $mycfg->userrole;
        $invitedata->timestart = time();
        $invitedata->timeend   = time() + 2 * datetime::DAY;

        $result = util::create_invitation($invitedata);
        $this->assertTrue((bool) $result);

        // Get the invitation by courseid.
        $invitation = $DB->get_record('local_invitation', ['courseid' => $course->id]);
        $this->assertIsObject($invitation);

        // Now we delete the course. The invitation should be deleted too.
        $result = delete_course($course->id, false);
        $this->assertTrue($result);
        // We should not find the invitation in the database.
        $check = $DB->get_record('local_invitation', ['id' => $invitation->id]);
        $this->assertFalse($check);
    }
}
