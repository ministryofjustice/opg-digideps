Feature: PA client profile Notes

  Scenario: PA view client notes
    Given I load the application status from "pa-report-completed"
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000010" region
    And I click the ".client-profile-notes-toggle" element
    Then each text should be present in the corresponding region:
      | No notes    | client-profile-notes |

  Scenario: PA adds client notes
    Given I load the application status from "pa-report-completed"
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000010" region
    And I click the ".client-profile-notes-toggle" element
    Then  I click the ".add-notes-button" element
    # empty form
    When I fill in the following:
      | note_title          |  |
      | note_content        |  |
      | note_category       |  |
    And I press "note_save"
    Then the following fields should have an error:
      | note_title |
    # title > 150 chars form
    When I fill in the following:
      | note_title          | 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789 1 |
      | note_content        |  |
      | note_category       |  |
    And I press "note_save"
    Then the following fields should have an error:
      | note_title |
    Then I fill in the following:
      | note_title          | test title  |
      | note_content        | test content |
      | note_category       | DWP |
    And I press "note_save"
    Then the form should be valid
    Then each text should be present in the corresponding region:
      | DWP | client-profile-notes |
      # Added By Initials
      | JG | client-profile-notes |
    And I click the ".note-title-expand" element
    Then each text should be present in the corresponding region:
      | test content | client-profile-notes |
