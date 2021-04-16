Feature: deputy / acl / security on pages

  @deputy
  Scenario: create another user with client and report with data
    # restore status of first report before submitting
    Given I load the application status from "report-submit-pre"
    And I add the following users to CASREC:
      | Case     | Surname | Deputy No | Dep Surname | Dep Postcode | Typeofrep |
      | 12345ABC | Client  | D003      | User        | SW1H 9AJ     | OPG102    |
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And the following users exist:
      | ndr | deputyType | firstName | lastName | email | postCode | activated |
      | disabled | LAY | Malicious | User | behat-malicious@publicguardian.gov.uk | SW1H 9AJ | true |
    Given I am logged in as "behat-malicious@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I set the user details to:
      | name    | Malicious        | User          |        |          |    |
      | address | 102 Petty France | MOJ           | London | SW1H 9AJ | GB |
      | phone   | 020 3334 3555    | 020 1234 5678 |        |          |    |
    When I set the client details with:
      | name       | Malicious      | Client      |            |         |    |
      | caseNumber | 12345ABC       |             |            |         |    |
      | courtDate  | 1              | 1           | 2016       |         |    |
      | address    | 1 South Parade | First Floor | Nottingham | NG1 2HT | GB |
      | phone      | 0123456789     |             |            |         |    |
    And I press "client_save"
    And the form should be valid
    And I fill in the following:
      | report_startDate_day   | 01   |
      | report_startDate_month | 01   |
      | report_startDate_year  | 2016 |
      | report_endDate_day     | 31   |
      | report_endDate_month   | 12   |
      | report_endDate_year    | 2016 |
    And I press "report_save"
    Then the form should be valid
    And I click on "report-start"
    And I save the report as "malicious user report"

  @deputy
  Scenario: Malicious User cannot access other's pages
    # behat-lay-deputy-102 can access report n.2
    Given I am logged in as "behat-lay-deputy-102@publicguardian.gov.uk" with password "DigidepsPass1234"
    Then the following "102 report" report pages should return the following status:
      | overview         | 200 |
      # decisions
      | decisions        | 200 |
      # contacts
      | contacts         | 200 |
      | contacts/add     | 200 |
      # assets
      | assets           | 200 |
      | assets/step-type | 200 |
      # accounts
      | bank-accounts    | 200 |
    # behat-malicious CANNOT access the same URLs
    Given I am logged in as "behat-malicious@publicguardian.gov.uk" with password "DigidepsPass1234"
    # reload the status (as some URLs calls might have deleted data)
    Then the following "102 report" report pages should return the following status:
      | overview                | 404 |
      | decisions               | 404 |
      | contacts                | 404 |
      | assets                  | 404 |
      | bank-accounts           | 404 |
      | declaration             | 404 |
      | submitted               | 404 |
    And the following "malicious user report" report pages should return the following status:
      | overview                | 200 |
      | decisions               | 200 |
      | contacts                | 200 |
      | assets                  | 200 |
