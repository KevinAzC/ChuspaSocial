@local @local_chuspasocial
Feature: Access to the ChuspaSocial wall
  In order to use the social wall
  As a student
  I need to be able to open the ChuspaSocial page

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | One      | student1@example.com |

  Scenario: A student visits the wall page
    Given I log in as "student1"
    When I visit "/local/chuspasocial/index.php"
    Then I should see "ChuspaSocial"