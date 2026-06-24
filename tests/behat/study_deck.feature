@local @local_chemillusion
Feature: Study decks
  In order to study sets of molecules
  As a user
  I need to reach the study decks area

  Scenario: A logged-in user can open the study decks page
    Given I log in as "admin"
    And I am on the "local/chemillusion/index.php" page
    When I follow "Study decks"
    Then I should see "Study decks"
