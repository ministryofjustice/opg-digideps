Feature: Contacts

  Scenario: A user has no contacts
    Given the following court orders exist:
      | client   | deputy    | deputy_type | report_type        | court_date |
      | 11223344 | RyanAdams | LAY         | Health and Welfare | 2018-01-30 |
    And "RyanAdams@behat-test.com" logs in
    And I view and start the contacts report section
    When I select "no" from "contact_exist[hasContacts]"
    And I fill in "contact_exist_reasonForNoContacts" with "I had no need"
    And I press "Save and continue"
    Then I should be on the contacts summary page
