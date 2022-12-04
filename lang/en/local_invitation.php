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

$string['available_from'] = 'Available from:';
$string['available_to'] = 'Available to:';
$string['back_to_course'] = 'Back to your course';
$string['clipboard'] = 'Clipboard';
$string['consent'] = 'Consent (optional)';
$string['consent_help'] = 'If you define a consent text a checkbox appears to the invited users which must be checked by them.';
$string['consent_title'] = 'Confirmation';
$string['current_invitation'] = 'Current invitation';
$string['current_invitation_note'] = 'If you create a new invitation, the old one will automatically become invalid.';
$string['delete_after_logout'] = 'Delete after logout';
$string['delete_after_logout_help'] = 'Delete the user immediately after logging out. With this active you cannot see this user in your course logs.';
$string['delete_confirmation'] = 'Do you want to delete this invitation? After that, the invitation link can not be used anymore.';
$string['delete_invitation'] = 'Delete invitation';
$string['edit_invitation'] = 'Edit invitation for temporary course access';
$string['error_could_not_create_and_enrol'] = 'Error: Could not create and enrol new user!';
$string['error_invalid_invitation'] = 'Error: This invitation is not valid!';
$string['error_invitation_not_active'] = 'Error: The invitation feature is not activated on this system.';
$string['error_timeend_can_not_be_before_timestart'] = 'The invitation can not end before it starts.';
$string['error_timeend_can_not_be_in_past'] = 'The invitation can not end in the past.';
$string['error_userrole_not_defined'] = 'The userrole is not defined in config. Please contact your administrator!';
$string['expiration_time'] = 'Expiration time (days)';
$string['expiration_time_help'] = 'A user account created by this plugin will expire after the defined amount of days. A expired account will be anonymized and deleted afterwards.';
$string['free_slots'] = 'Free slots:';
$string['guestuser_suffix'] = '(Guest user)';
$string['info_already_loggedin'] = 'You are already logged in. If you continue you will be logged out.';
$string['invitation'] = 'Invitation';
$string['invitation:manage'] = 'Manage invitations';
$string['invitation_created'] = 'Invitation successfully created.';
$string['invitation_delete_note_timeandlogout'] = 'The temporary account and the associated data are automatically deleted as soon as the user logs out, but at the latest after {$a}.';
$string['invitation_delete_note_timeonly'] = 'The temporary access and the associated data are automatically deleted after {$a}.';
$string['invitation_deleted'] = 'Invitation successfully deleted.';
$string['invitation_note'] = 'You can use the invitation function to grant temporary access to this course. The created invitation link enables users without a regular account to access this course.';
$string['invitation_updated'] = 'Invitation successfully updated.';
$string['invite_participants'] = 'New invitation for temporary course access';
$string['join'] = 'Join';
$string['link_copied'] = 'The link has been copied.';
$string['max_users'] = 'Maximum users';
$string['max_users_per_invitation'] = 'Maximum users per invitation';
$string['nameinfo'] = 'Name info';
$string['nameinfo_help'] = 'With this text you can define a short explanation how to use the Name field in the join formular.';
$string['note'] = 'Note:';
$string['pluginname'] = 'Invitation';
$string['preventactions'] = 'Prevent actions';
$string['preventactions_help'] = '<strong>Note:</strong> You can define urls or parts of urls which invited users should not be able to call.<br>
If the invited user calls such url he is redirected to its course or to the Moodle frontpage.<br>
You should not include the Dashboard or the frontpage in this list!';
$string['qrcode'] = 'QR Code';
$string['remove_old_invitations'] = 'Remove old users';
$string['showqrcode'] = 'Show QR Code';
$string['single_name_field'] = 'Single name field';
$string['single_name_field_help'] = 'When "single name field" is checked, the user only needs to specify a single name and not a first and last name. In addition a suffix is appended to the name.';
$string['systemrole'] = 'System role';
$string['systemrole_help'] = 'If a system role is defined the invited user will be assigned to this. This can be used to restrict the invited user on system level.';
$string['timeend'] = 'Time end';
$string['timestart'] = 'Time start';
$string['usedslots'] = 'Used:';
$string['userrole'] = 'User role';
$string['welcome_note'] = 'Welcome {$a->firstname} {$a->lastname}!';
