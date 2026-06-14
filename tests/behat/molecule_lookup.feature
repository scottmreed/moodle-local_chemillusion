@local @local_chemillusion
Feature: Molecule lookup tools
  In order to study chemistry inside Moodle
  As a user
  I need to reach the molecule lookup tool

  Scenario: A logged-in user can open the study tools page
    Given I log in as "admin"
    And I follow "ChemIllusion study tools"
    When I follow "Molecule lookup & study tools"
    Then I should see "Molecule name, SMILES, InChI, or InChIKey"
    And I should see "Look up"
