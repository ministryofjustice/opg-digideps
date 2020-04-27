Feature: Synchronising Documents with Sirius
  As a case manager
  So I can carry out my supervision role efficiently
  I need report PDFs and supporting documents to be automatically synced with Sirius when a user submits a report

  Scenario: Create court orders for the feature
    Given the following court orders exist:
      | client   | deputy   | deputy_type | report_type                                | court_date |
      | 12121212 | DeputyA | LAY          | Property and Financial Affairs High Assets | 2018-01-30 |
      | 23232323 | DeputyB | LAY          | Health and Welfare                         | 2018-01-30 |
      | 34343434 | DeputyC | LAY          | NDR                                        | 2018-01-30 |
      | 45454545 | DeputyD | PROF         | Property and Financial Affairs High Assets | 2018-01-30 |
      | 56565656 | DeputyE | PROF         | Health and Welfare                         | 2018-01-30 |
      | 67676767 | DeputyF | PA           | Property and Financial Affairs High Assets | 2018-01-30 |
      | 78787878 | DeputyG | PA           | Health and Welfare                         | 2018-01-30 |

  Scenario Outline: Submitting a report sets the synchronisation status to queued
    Given I have the "2018" to "2019" report between "<deputy>" and "<case_number>"
    And the report has been submitted
    And I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    When I view the submissions page
    And I click on "tab-pending"
    Then I should see "<case_number>"
    And the documents should be queued
    Examples:
      | case_number | deputy  |
      | 12121212    | DeputyA |
      | 23232323    | DeputyB |
      | 34343434    | DeputyC |
      | 45454545    | DeputyD |
      | 56565656    | DeputyE |
      | 67676767    | DeputyF |
      | 78787878    | DeputyG |
