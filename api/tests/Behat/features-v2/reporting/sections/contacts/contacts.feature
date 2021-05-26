#@v2 @contacts
#Feature: Contacts
#
#  @super-admin
#  Scenario: A user has no contacts
#    Given the following court orders exist:
#      | client   | deputy    | deputy_type | report_type        | court_date |
#      | 11223344 | RyanAdams | LAY         | Health and Welfare | 2018-01-30 |
#    And "RyanAdams@behat-test.com" logs in
#    When I view and start the contacts report section
#    And there are no contacts to add
#    Then I should be on the contacts summary page
#    And the contacts summary page should contain the details I entered
#
#  @super-admin
#  Scenario: The section navigation links are correctly displayed
#    Given the following court orders exist:
#      | client   | deputy       | deputy_type | report_type        | court_date |
#      | 11223344 | AlunaFrancis | LAY         | Health and Welfare | 2018-01-30 |
#    And "AlunaFrancis@behat-test.com" logs in
#    When I view the contacts report section
#    Then the previous section should be "Decisions"
#    And the next section should be "Visits and Care"
#
#  @super-admin
#  Scenario: Adding one contact
#    Given the following court orders exist:
#      | client   | deputy     | deputy_type | report_type        | court_date |
#      | 11223344 | GeorgeReid | LAY         | Health and Welfare | 2018-01-30 |
#    And "GeorgeReid@behat-test.com" logs in
#    When I view and start the contacts report section
#    And there are contacts to add
#    And I enter valid contact details
#    And there are no further contacts to add
#    Then the contacts summary page should contain the details I entered
#
#  @super-admin
#  Scenario: Adding multiple contacts
#    Given the following court orders exist:
#      | client   | deputy     | deputy_type | report_type        | court_date |
#      | 11223344 | GeorgeReid | LAY         | Health and Welfare | 2018-01-30 |
#    And "GeorgeReid@behat-test.com" logs in
#    When I view and start the contacts report section
#    And there are contacts to add
#    And I enter valid contact details
#    And I enter another contacts details
#    And there are no further contacts to add
#    Then the contacts summary page should contain the details I entered
