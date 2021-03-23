@mod @mod_library @_file_upload
Feature: Importing library entries
  In order to add library entries by bulk
  As a teacher
  I need to be able to import library entries from a file

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "users" exist:
      | username    | firstname | lastname | email            |
      | teacher1    | Terry1    | Teacher1 | teacher1@example.com |
    And the following "course enrolments" exist:
      | user        | course | role           |
      | teacher1    | C1     | editingteacher |
    And the following "activities" exist:
      | activity | course | idnumber  | name        |
      | library | C1     | library1 | Library 1  |

  @javascript @block_recent_activity
  Scenario: Importing library entries and checking the Recent activity block
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Recent activity" block
    And I follow "Library 1"
    And I navigate to "Import entries" in current page administration
    And I upload "mod/library/tests/fixtures/texfilter_library_en.xml" file to "File to import" filemanager
    When I press "Submit"
    Then I should see "103" in the "Total entries:" "table_row"
    And I should see "103" in the "Imported entries:" "table_row"
    And I am on "Course 1" course homepage
    And I should see "Added Library" in the "Recent activity" "block"
    And I should see "New library entries:" in the "Recent activity" "block"

  @javascript @block_tags
  Scenario: Importing library entries and checking Tags block
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Tags" block
    And I follow "Library 1"
    And I navigate to "Import entries" in current page administration
    And I upload "mod/library/tests/fixtures/musicians.xml" file to "File to import" filemanager
    When I press "Submit"
    And I am on "Course 1" course homepage
    And I click on "Beatles" "link" in the "Tags" "block"
    Then I should see "Paul McCartney"
