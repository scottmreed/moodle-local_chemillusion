@local @local_chemillusion @javascript
Feature: RDKit WASM study cards (Phase 1B)
  In order to study structures locally
  As a user
  I need the RDKit-enhanced tools page to load its regions

  Background:
    Given the following config values are set as admin:
      | enable_rdkit | 1 | local_chemillusion |

  Scenario: The tools page exposes the live results and structure regions
    Given I log in as "admin"
    And I am on the "local/chemillusion/index.php" page
    When I follow "Molecule lookup & study tools"
    Then "#local-chemillusion-results" "css_element" should exist
    And I should see "Look up"
