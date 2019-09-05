Feature: PA client archive

  Scenario: PA archives a client
    Given I load the application status from "team-users-complete"
    And I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-00900009" region
    # archive-cancel
    And I click on "client-archive"
    Then I should see the "confirm-cancel" link
    When I click on "confirm-cancel"
    Then the URL should match "report/\d+/overview"
    # archive-no-confirm
    When I click on "client-archive"
    And I press "org_client_archive_save"
    Then the following fields should have an error:
      | org_client_archive_confirmArchive   |
    # correct form
    When I fill in the following:
      | org_client_archive_confirmArchive | 1 |
    And I press "org_client_archive_save"
    Then the form should be valid
    And the URL should match "/org"
    And I should see "The client has been archived"
    But I should not see the "client-00900009" region

  Scenario: CSV re-upload
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
      # upload PA users
    When I click on "admin-upload-pa"
    And I attach the file "behat-pa.csv" to "admin_upload_file"
    And I press "admin_upload_upload"
    Then the form should be valid
      # assert archived is shown in PA dashboard
    Given I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
    Then I should not see the "client-009 00009" region
