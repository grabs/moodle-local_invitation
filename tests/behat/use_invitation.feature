@local @local_invitation
Feature: Use an invitation as guest user
  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | teacher1 | Teacher   | 1        |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
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
  Scenario: Use the Invitation link as guest
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I should see "New invitation for temporary course access"
    And I click on "New invitation for temporary course access" "link" in the "#nav-drawer" "css_element"
    And I should see "New invitation for temporary course access"
    And I should see "Maximum users"
    And I set the field "Maximum users" to "5"
    And I press "Save changes"
    And I should see "Current invitation"
    And I visit "#region-main div.invitation-url > a" "css_element" after logout
    Then I should see "Invitation"
    And I set the field "Name" to "George"
    And I press "Join"
    And I should see "Welcome George"
