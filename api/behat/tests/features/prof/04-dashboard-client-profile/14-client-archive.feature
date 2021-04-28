Feature: PROF client archive

  Scenario: PROF archives a client
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234!!"
    And I fill in "search" with "31000016"
    And I press "search_submit"
    And I click on "pa-report-open" in the "client-31000016" region

    # archive-cancel
    When I click on "client-archive"
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
    And I should not see the "client-31000016" region
