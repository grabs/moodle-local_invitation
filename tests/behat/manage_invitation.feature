@local @local_invitation
Feature: Create new and delete existing invitations
  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | teacher1 | Teacher   | 1        |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "groups" exist:
        | course | name         | idnumber |
        | C1     | Group-A-Test | GA       |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following config values are set as admin:
      | active            | 1  | local_invitation |
      | deleteafterlogout | 0  | local_invitation |
      | expiration        | 1  | local_invitation |
      | maxusers          | 15 | local_invitation |
      | singlenamefield   | 1  | local_invitation |

  @javascript
  Scenario: Create an invitation with an existing group
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "#topofscroll nav.moremenu li[data-region=\"morebutton\"] > a" "css_element"
    And I should see "New invitation for temporary course access"
    And I click on "New invitation for temporary course access" "link" in the "#topofscroll nav.moremenu" "css_element"
    And I should see "New invitation for temporary course access"
    And I should see "Maximum users"
    And I set the field "Maximum users" to "5"
    And I click on "Use group" "checkbox"
    And I set the field "Group" to "Group-A-Test"
    And I press "Save changes"
    Then I should see "Invitation successfully created."

  @javascript
  Scenario: Create an invitation with a new group
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "#topofscroll nav.moremenu li[data-region=\"morebutton\"] > a" "css_element"
    And I should see "New invitation for temporary course access"
    And I click on "New invitation for temporary course access" "link" in the "#topofscroll nav.moremenu" "css_element"
    And I should see "New invitation for temporary course access"
    And I should see "Maximum users"
    And I set the field "Maximum users" to "5"
    And I click on "Use group" "checkbox"
    And I set the field "Group" to "New Group 1"
    And I press "Save changes"
    And I should see "Invitation successfully created."
    And I am on the "Course 1" "groups" page
    And I should see "New Group 1"

  @javascript
  Scenario: Delete an invitation
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "#topofscroll nav.moremenu li[data-region=\"morebutton\"] > a" "css_element"
    And I should see "New invitation for temporary course access"
    And I click on "New invitation for temporary course access" "link" in the "#topofscroll nav.moremenu" "css_element"
    And I should see "New invitation for temporary course access"
    And I should see "Maximum users"
    And I set the field "Maximum users" to "5"
    And I press "Save changes"
    And I should see "Invitation successfully created."
    And I am on "Course 1" course homepage
    And I click on "#topofscroll nav.moremenu li[data-region=\"morebutton\"] > a" "css_element"
    And I should see "Edit invitation for temporary course access"
    And I click on "Edit invitation for temporary course access" "link" in the "#topofscroll nav.moremenu" "css_element"
    And I should see "Current invitation"
    And I click on "Delete invitation" "link" in the "#region-main .card.invitationsettings" "css_element"
    And I should see "Do you want to delete this invitation"
    # Click on the single button "Delete".
    And I click on ".modal.fade.show #id_submitbutton" "css_element"
    Then I should see "Invitation successfully deleted."
