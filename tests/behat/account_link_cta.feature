@local @local_chemillusion
Feature: Account-link funnel
  In order to upgrade to ChemIllusion
  As a user
  I should see the account-link call to action only when enabled

  Background:
    Given the following config values are set as admin:
      | enable_account_linking | 1 | local_chemillusion |

  Scenario: The connect CTA appears when linking is enabled
    Given I log in as "admin"
    When I am on the "local/chemillusion/index.php" page
    Then I should see "Connect ChemIllusion"

  Scenario: The link page offers the connect action
    Given I log in as "admin"
    And I am on the "local/chemillusion/index.php" page
    When I follow "Connect ChemIllusion"
    Then I should see "Link your ChemIllusion account"
