Feature: PROF client profile Notes

  Scenario: PROF view client notes
    Given I load the application status from "prof-users-uploaded"
    And I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000011" region
    Then each text should be present in the corresponding region:
      | No notes    | client-profile-notes |

  Scenario: PROF adds client notes
    Given I load the application status from "prof-users-uploaded"
    And I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000011" region
    And I click on "add-notes-button" in the "client-profile-notes" region
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
    Then each text should be present in the corresponding region:
      | test content | client-profile-notes |

  Scenario: PROF edit client notes
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000011" region
    And I click on "edit-notes-button" in the "client-profile-notes" region
    Then the following fields should have the corresponding values:
      | note_title          | test title  |
      | note_content        | test content |
      | note_category       | DWP |
    Then I fill in the following:
      | note_title          | test title edit |
      | note_content        | test content edit |
      | note_category       | OPG |
    And I press "note_save"
    Then the form should be valid
    Then each text should be present in the corresponding region:
      | OPG | client-profile-notes |
      # Added By Initials
      | JG | client-profile-notes |
    Then each text should be present in the corresponding region:
      | test content edit | client-profile-notes |

  Scenario: PROF delete client notes
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000011" region
    And I save the current URL as "report-overview"
    And I click on "delete-notes-button" in the "client-profile-notes" region
    Then the URL should match "/note/\d+/delete"
    # test cancel button on confirmation page
    When I click on "confirm-cancel"
    Then I go to the URL previously saved as "report-overview"
    Then I click on "delete-notes-button" in the "client-profile-notes" region
    Then the response status code should be 200
    And I click on "note-delete"
    Then the form should be valid
    Then I go to the URL previously saved as "report-overview"
    And I should not see "test title content" in the "client-profile-notes" region

