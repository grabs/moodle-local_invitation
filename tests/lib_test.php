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

        parent::setUp();

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
     * Test to create and delete invitations without using a group.
     *
     * @covers \local_invitation\helper\util
     * @return void
     */
    public function test_create_invitation_without_group(): void {
        $CFG   = gl::cfg();
        $DB    = gl::db();
        $mycfg = gl::mycfg();

        // Generate for each example a new course + invitation.
        foreach ($this->examples as $example) {
            $course = $this->getDataGenerator()->create_course();

            // Simulate the form data for creating a new invitation.
            $result = $this->create_invitation(
                $course->id,
                $example->maxusers,
                $mycfg->userrole,
                '',
                0
            );

            $this->assertTrue((bool) $result);

            // Show that the groupid is "0" in the new invitation record.
            $invitation = $DB->get_record('local_invitation', ['courseid' => $course->id, 'groupid' => 0]);
            $this->assertNotEmpty($invitation);
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
     * Test to create and delete invitations and using a new group.
     *
     * @covers \local_invitation\helper\util
     * @return void
     */
    public function test_create_invitation_with_new_group(): void {
        $CFG   = gl::cfg();
        $DB    = gl::db();
        $mycfg = gl::mycfg();

        $testgroupname = 'testgroup';

        // Generate for each example a new course + invitation.
        foreach ($this->examples as $example) {
            $course = $this->getDataGenerator()->create_course();

            // Show that there is no testgroup before the creation.
            $countgroups = $DB->count_records('groups', ['courseid' => $course->id, 'name' => $testgroupname]);
            $this->assertEquals(0, $countgroups);

            // Simulate the form data for creating a new invitation.
            $result = $this->create_invitation(
                $course->id,
                $example->maxusers,
                $mycfg->userrole,
                $testgroupname,
                -1
            );

            $this->assertTrue((bool) $result);

            // Show that now there is the testgroup after the creation.
            $testgroup = $DB->get_record('groups', ['courseid' => $course->id, 'name' => $testgroupname]);
            $this->assertNotEmpty($testgroup);

            // Show that the groupid is in the new invitation record.
            $invitation = $DB->get_record('local_invitation', ['courseid' => $course->id, 'groupid' => $testgroup->id]);
            $this->assertNotEmpty($invitation);
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
     * Test to create and delete invitations and using an existing group.
     *
     * @covers \local_invitation\helper\util
     * @return void
     */
    public function test_create_invitation_with_existing_group(): void {
        $CFG   = gl::cfg();
        $DB    = gl::db();
        $mycfg = gl::mycfg();

        $testgroupname = 'testgroup';

        // Generate for each example a new course + invitation.
        foreach ($this->examples as $example) {
            $course = $this->getDataGenerator()->create_course();

            // Create the testgroup before creating the invitations.
            $groupid = $this->create_group($course->id, $testgroupname);

            // Simulate the form data for creating a new invitation.
            $result = $this->create_invitation(
                $course->id,
                $example->maxusers,
                $mycfg->userrole,
                '',
                $groupid
            );

            $this->assertTrue((bool) $result);

            // Show that the groupid is in the new invitation record.
            $invitation = $DB->get_record('local_invitation', ['courseid' => $course->id, 'groupid' => $groupid]);
            $this->assertNotEmpty($invitation);
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
    public function test_use_invitation_without_group(): void {
        $PAGE  = gl::page();
        $DB    = gl::db();
        $mycfg = gl::mycfg();

        $course = $this->getDataGenerator()->create_course();

        // Simulate the form data for creating a new invitation.
        $result = $this->create_invitation(
            $course->id,
            $this->examples[0]->maxusers,
            $mycfg->userrole,
            '',
            0
        );
        $this->assertTrue((bool) $result);

        // Lets get the the invitation-secret by using the courseid.
        $invitationsecret = $DB->get_field('local_invitation', 'secret', ['courseid' => $course->id]);
        $this->assertIsString($invitationsecret);

        // Now we get the invitation by using the secret.
        // This is also checking the time.
        $invitation = util::get_invitation_from_secret($invitationsecret, $course->id);
        $this->assertIsObject($invitation);

        // Simulate the confirm form.
        $newuser = $this->use_invitation($invitation);
        $this->assertIsObject($newuser);

        /** @var \local_invitation\output\renderer $output */
        $output = $PAGE->get_renderer('local_invitation');
        $this->assertIsObject($output);

        // Generate the welcome note.
        $welcomenote = $output->render(new \local_invitation\output\component\welcome_note($newuser));
        $this->assertIsString($welcomenote);
    }

    /**
     * Test to use an invitation as user with group.
     *
     * @covers \local_invitation\helper\util
     * @return void
     */
    public function test_use_invitation_with_group(): void {
        $PAGE  = gl::page();
        $DB    = gl::db();
        $mycfg = gl::mycfg();

        $course = $this->getDataGenerator()->create_course();
        $testgroupname = 'testgroup';

        // Simulate the form data for creating a new invitation.
        $result = $this->create_invitation(
            $course->id,
            $this->examples[0]->maxusers,
            $mycfg->userrole,
            $testgroupname,
            -1
        );
        $this->assertTrue((bool) $result);

        // Lets get the the invitation-secret by using the courseid.
        $invitationsecret = $DB->get_field('local_invitation', 'secret', ['courseid' => $course->id]);
        $this->assertIsString($invitationsecret);

        // Now we get the invitation by using the secret.
        // This is also checking the time.
        $invitation = util::get_invitation_from_secret($invitationsecret, $course->id);
        $this->assertIsObject($invitation);

        // Simulate the confirm form.
        $newuser = $this->use_invitation($invitation);
        $this->assertIsObject($newuser);

        // Check whether the user is in the testgroup.
        $testgroup = $DB->get_record('groups', ['courseid' => $course->id, 'name' => $testgroupname]);
        $this->assertNotEmpty($testgroup);
        $ismember = $DB->record_exists('groups_members', ['groupid' => $testgroup->id, 'userid' => $newuser->id]);
        $this->assertTrue($ismember);

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
        $result = $this->create_invitation(
            $course->id,
            $this->examples[0]->maxusers,
            $mycfg->userrole,
            '',
            0
        );
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

    /**
     * Creates a new invitation for a course.
     *
     * @param int $courseid The ID of the course to create the invitation for.
     * @param int $maxusers The maximum number of users that can be invited.
     * @param int $userrole The role ID of the user role that can be invited.
     * @param string $groupname The name of the group to which the invitation should be limited.
     * @param int $groupid The ID of the group to which the invitation should be limited. If 0, no group is specified.
     * @return bool True if the invitation is created successfully, false otherwise.
     */
    protected function create_invitation(int $courseid, int $maxusers, int $userrole, string $groupname, int $groupid): bool {
        $invitedata            = new \stdClass();
        $invitedata->courseid  = $courseid;
        $invitedata->maxusers  = $maxusers;
        $invitedata->userrole  = $userrole;
        $invitedata->timestart = time();
        $invitedata->timeend   = time() + 2 * datetime::DAY;
        $invitedata->usegroup  = ($groupid !== 0);
        $invitedata->groupid   = $groupid;
        $invitedata->groupname = $groupname;

        return (bool) util::create_invitation($invitedata);
    }

    /**
     * Creates a new group in a given course.
     *
     * @param int $courseid The ID of the course where the group will be created.
     * @param string $groupname The name of the group to be created.
     *
     * @return int The ID of the newly created group.
     */
    protected function create_group(int $courseid, string $groupname): int {
        $groupdata = new \stdClass();
        $groupdata->courseid = $courseid;
        $groupdata->name = $groupname;
        $groupdata->description = get_string('group_created_by_invitation', 'local_invitation');
        $groupdata->descriptionformat = FORMAT_HTML;
        $groupdata->enrolmentkey = '';
        return groups_create_group($groupdata);
    }

    /**
     * Simulates the use of an invitation to create and login a new user.
     *
     * This function creates a new user with the given firstname, lastname, and consent,
     * and then enrolls the user in the course associated with the given invitation.
     * After the user is created and enrolled, the function logs in the user.
     *
     * @param \stdClass $invitation The invitation object containing information about the course and user role.
     * @return \stdClass The newly created and logged-in user.
     *
     * @throws \coding_exception If there is an error creating or enrolling the user.
     * @throws \dml_exception If there is an error logging in the user.
     */
    protected function use_invitation($invitation) {
        $confirmdata            = new \stdClass();
        $confirmdata->firstname = 'George';
        $confirmdata->lastname  = 'Meyer';
        $confirmdata->consent   = true;
        // Create and login the new user.
        return util::create_login_and_enrol($invitation, $confirmdata);
    }
}
