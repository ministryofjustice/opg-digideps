Feature: PA client archive

  Scenario: PA archives a client
    Given I load the application status from "team-users-complete"
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000016" region

    # archive-cancel
    When I click on "client-archive"
    Then I should see the "confirm-cancel" link
    When I click on "confirm-cancel"
    Then the URL should match "report/\d+/overview"

    # archive-no-confirm
    When I click on "client-archive"
    And I press "org_client_archive_save"
    Then the following fields should have an error:
      | pa_client_archive_confirmArchive   |

    # correct form
    When I fill in the following:
      | pa_client_archive_confirmArchive | 1 |
    And I press "org_client_archive_save"
    Then the form should be valid
    And I should be on "/pa/"
    And I should see "The client has been archived"
    And I should not see the "client-01000016" region