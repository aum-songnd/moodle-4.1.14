@mod @mod_thpage
Feature: Configure thpage appearance
  In order to change the appearance of the thpage resource
  As an admin
  I need to configure the thpage appearance settings

  Background:
    Given the following "courses" exist:
      | shortname | fullname   |
      | C1        | Course 1 |
    And the following "activities" exist:
      | activity | name       | intro      | course | idnumber |
      | thpage     | thpageName1  | thpageDesc1  | C1     | thpage1    |
    And I log in as "admin"

  @javascript
  Scenario: Hide and display the thpage name
    Given I am on "Course 1" course homethpage
    When I follow "thpageName1"
    Then I should see "thpageName1" in the "region-main" "region"
    And I navigate to "Edit settings" in current thpage administration
    And I follow "Appearance"
    When I click on "Display thpage name" "checkbox"
    And I press "Save and display"
    Then I should not see "thpageName1" in the "region-main" "region"
    And I navigate to "Edit settings" in current thpage administration
    And I follow "Appearance"
    When I click on "Display thpage name" "checkbox"
    And I press "Save and display"
    Then I should see "thpageName1" in the "region-main" "region"

  @javascript
  Scenario: Display and hide the thpage description
    Given I am on "Course 1" course homethpage
    When I follow "thpageName1"
    Then I should not see "thpageDesc1" in the "region-main" "region"
    And I navigate to "Edit settings" in current thpage administration
    And I follow "Appearance"
    When I click on "Display thpage description" "checkbox"
    And I press "Save and display"
    Then I should see "thpageDesc1" in the "region-main" "region"
    And I navigate to "Edit settings" in current thpage administration
    And I follow "Appearance"
    When I click on "Display thpage description" "checkbox"
    And I press "Save and display"
    Then I should not see "thpageDesc1" in the "region-main" "region"

  @javascript
  Scenario: Display and hide the last modified date
    Given I am on "Course 1" course homethpage
    When I follow "thpageName1"
    Then I should see "Last modified:" in the "region-main" "region"
    And I navigate to "Edit settings" in current thpage administration
    And I follow "Appearance"
    When I click on "Display last modified date" "checkbox"
    And I press "Save and display"
    Then I should not see "Last modified:" in the "region-main" "region"
    And I navigate to "Edit settings" in current thpage administration
    And I follow "Appearance"
    When I click on "Display last modified date" "checkbox"
    And I press "Save and display"
    Then I should see "Last modified:" in the "region-main" "region"
