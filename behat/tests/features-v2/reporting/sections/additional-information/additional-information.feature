@v2
Feature: Additional Information

  Scenario: A user has no additional information to add
    Given the following court orders exist:
      | client   | deputy    | deputy_type | report_type        | court_date |
      | 11223344 | KristineJones | LAY         | Health and Welfare | 2018-01-30 |
    And "KristineJones@behat-test.com" logs in
    When I view and start the additional information report section
    And there is no additional information to add
    Then I should be on the additional information summary page
    And the additional information summary page should contain the details I entered

  Scenario: The section navigation links are correctly displayed
    Given the following court orders exist:
      | client   | deputy       | deputy_type | report_type        | court_date |
      | 11223344 | OlufemiKamau | LAY         | Health and Welfare | 2018-01-30 |
    And "OlufemiKamau@behat-test.com" logs in
    When I view the additional information report section
    Then the previous section should be "Actions"
    And the next section should be "Documents"

  Scenario: Adding additional information
    Given the following court orders exist:
      | client   | deputy     | deputy_type | report_type        | court_date |
      | 11223344 | KiranSharma | LAY         | Health and Welfare | 2018-01-30 |
    And "KiranSharma@behat-test.com" logs in
    When I view and start the additional information report section
    And there is additional information to add
    Then the additional information summary page should contain the details I entered
