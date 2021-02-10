Feature: Contacts

  Scenario: A user has no contacts
    Given the following court orders exist:
      | client   | deputy    | deputy_type | report_type        | court_date |
      | 11223344 | RyanAdams | LAY         | Health and Welfare | 2018-01-30 |
    And "RyanAdams@behat-test.com" logs in
    And I view and start the contacts report section
    And there are no contacts to add
    Then I should be on the contacts summary page
    Then the summary page should contain the information I entered

  Scenario: The section navigation links are correctly displayed
    Given the following court orders exist:
      | client   | deputy       | deputy_type | report_type        | court_date |
      | 11223344 | AlunaFrancis | LAY         | Health and Welfare | 2018-01-30 |
    And "AlunaFrancis@behat-test.com" logs in
    And I view the contacts report section
    And the previous section should be "Decisions"
    And the next section should be "Visits and Care"

  Scenario: Adding one contact
    Given the following court orders exist:
      | client   | deputy     | deputy_type | report_type        | court_date |
      | 11223344 | GeorgeReid | LAY         | Health and Welfare | 2018-01-30 |
    And "GeorgeReid@behat-test.com" logs in
    And I view and start the contacts report section
    And there are contacts to add
    And I enter valid contact information
    And there are no further contacts to add
    Then the summary page should contain the information I entered

  Scenario: Adding multiple contacts
    Given the following court orders exist:
      | client   | deputy     | deputy_type | report_type        | court_date |
      | 11223344 | GeorgeReid | LAY         | Health and Welfare | 2018-01-30 |
    And "GeorgeReid@behat-test.com" logs in
    And I view and start the contacts report section
    And there are contacts to add
    Then I should be on the add a contact page
    When I enter valid contact information
    And I enter another contacts details
    And there are no further contacts to add
    Then the summary page should contain the information I entered
