@local @local_chemillusion
Feature: ChemIllusion admin settings
  In order to control how ChemIllusion behaves
  As an administrator
  I need to reach and read the plugin settings

  Scenario: Administrator can open the ChemIllusion settings page
    Given I log in as "admin"
    When I navigate to "Plugins > Local plugins > ChemIllusion Study Cards" in site administration
    Then I should see "Operating mode"
    And I should see "External services"
    And I should see "Privacy"
    And I should see "Disable all external calls"
