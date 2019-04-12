Feature: deputy / acl / security on pages

  @deputy
  Scenario: create another user with client and report with data
    # restore status of first report before submitting
    Given emails are sent from "admin" area
    And I reset the email log
    Given I load the application status from "report-submit-pre"
    And I add the following users to CASREC:
      | Case     | Surname | Deputy No | Dep Surname | Dep Postcode | Typeofrep |
      | 12345ABC | Client  | D003      | User        | SW1H 9AJ     | OPG102    |
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    When I create a new "NDR-disabled" "Lay Deputy" user "Malicious" "User" with email "behat-malicious@publicguardian.gov.uk" and postcode "SW1H 9AJ"
    And I activate the user with password "Abcd1234"
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
    Then I press "client_save"
    And the form should be valid
    And I fill in the following:
      | report_startDate_day   | 01   |
      | report_startDate_month | 01   |
      | report_startDate_year  | 2016 |
      | report_endDate_day     | 31   |
      | report_endDate_month   | 12   |
      | report_endDate_year    | 2016 |
    And I press "report_save"
    And the form should be valid

  @deputy
  Scenario: Malicious User cannot access other's pages
    # behat-user can access report n.2
    Given I am logged in as "behat-user@publicguardian.gov.uk" with password "Abcd1234"
    And I save the application status into "deputy-acl-before"
    Then the following "client" pages should return the following status:
      | /report/11/overview         | 200 |
      # decisions
      | /report/11/decisions        | 200 |
      # contacts
      | /report/11/contacts         | 200 |
      | /report/11/contacts/add     | 200 |
      # assets
      | /report/11/assets           | 200 |
      | /report/11/assets/step-type | 200 |
      # accounts
      | /report/11/bank-accounts    | 200 |
    # behat-malicious CANNOT access the same URLs
    Given I am logged in as "behat-malicious@publicguardian.gov.uk" with password "Abcd1234"
    # reload the status (as some URLs calls might have deleted data)
    And I load the application status from "deputy-acl-before"
    Then the following "client" pages should return the following status:
      | /report/12/overview                | 200 |
      | /report/11/overview                | 500 |
      # decisions
      | /report/12/decisions               | 200 |
      | /report/11/decisions               | 500 |
      # contacts
      | /report/12/contacts                | 200 |
      | /report/11/contacts                | 500 |
      # assets
      | /report/12/assets                  | 200 |
      | /report/11/assets                  | 500 |
      # accounts
      | /report/11/bank-accounts           | 500 |
      # submit
      | /report/11/declaration             | 500 |
      | /report/11/submitted               | 500 |
    And I load the application status from "deputy-acl-before"


