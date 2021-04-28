Feature: As an admin user, in order to ensure correct report PDFs can always be generated, I need an option to regenerate the reports from within Digideps

  @deputy @download-reports
  Scenario: Non super user cannot download a report that has been submitted
    Given I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I visit the client page for "102"
    Then I should not see "Download"

  @deputy @download-reports
  Scenario: Super user can download a report that has been submitted
    Given I am logged in to admin as "super-admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I visit the client page for "102"
    And I follow "Download"
    Then the response status code should be 200
    And the response should have the "Content-Type" header containing "application/pdf"
    And the response should have the "Content-Disposition" header containing ".pdf"

  @deputy @download-reports
  Scenario: Case manager cannot download a non submitted report
    Given I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I visit the client page for "103-5"
    Then I should not see "Download"
