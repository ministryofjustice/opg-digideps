@docs
Feature: Synchronising Documents with Sirius
  As a case manager
  So I can carry out my supervision role efficiently
  I need report PDFs and supporting documents to be automatically synced with Sirius when a user submits a report

  Scenario: Create court orders for the feature
    Given the following court orders exist:
      | client   | deputy      | deputy_type  | report_type                                | court_date |
      | 12121212 | DeputyDocsA | LAY          | Property and Financial Affairs High Assets | 2018-01-30 |
      | 23232323 | DeputyDocsB | LAY          | Health and Welfare                         | 2018-01-30 |
      | 34343434 | DeputyDocsC | LAY          | NDR                                        | 2018-01-30 |
      | 45454545 | DeputyDocsD | PROF         | Property and Financial Affairs High Assets | 2018-01-30 |
      | 56565656 | DeputyDocsE | PROF         | Health and Welfare                         | 2018-01-30 |
      | 67676767 | DeputyDocsF | PA           | Property and Financial Affairs High Assets | 2018-01-30 |
      | 78787878 | DeputyDocsG | PA           | Health and Welfare                         | 2018-01-30 |

  Scenario Outline: Submitting a report sets the synchronisation status to queued
    Given I have the "2018" to "2019" report between "<deputy>" and "<case_number>"
    And the report has been completed
    And I attached a supporting document "test-image.png" to the completed report
    And I submit the report
    And I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I view the submissions page
    And I click on "tab-pending"
    Then I should see "<case_number>"
    And the report PDF document should be queued
    And the document "test-image.png" should be queued
    Examples:
      | case_number | deputy      |
      | 12121212    | DeputyDocsA |
      | 23232323    | DeputyDocsB |
      | 45454545    | DeputyDocsD |
      | 56565656    | DeputyDocsE |
      | 67676767    | DeputyDocsF |
      | 78787878    | DeputyDocsG |

  Scenario: Submitting an NDR sets the synchronisation status of the report PDF to queued
    Given I have the "2018" to "2019" report between "DeputyDocsC" and "34343434"
    And the report has been completed
    And I submit the report
    And I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I view the submissions page
    And I click on "tab-pending"
    Then I should see "34343434"
    And the report PDF document should be queued

    Scenario Outline: Submitting supporting documents after a report submission sets the synchronisation status to queued
      Given I have the "2018" to "2019" report between "<deputy>" and "<case_number>"
      Given I am logged in as "<emailAddress>" with password "DigidepsPass1234"
      And I attached a supporting document "test-image.png" to the submitted report
      And I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
      When I view the submissions page
      And I click on "tab-pending"
      Then I should see "<case_number>"
      And the document "test-image.png" should be queued
      Examples:
        | case_number | emailAddress               | deputy      |
        | 12121212    | DeputyDocsA@behat-test.com | DeputyDocsA |
        | 23232323    | DeputyDocsB@behat-test.com | DeputyDocsB |
        | 45454545    | DeputyDocsD@behat-test.com | DeputyDocsD |
        | 56565656    | DeputyDocsE@behat-test.com | DeputyDocsE |
        | 67676767    | DeputyDocsF@behat-test.com | DeputyDocsF |
        | 78787878    | DeputyDocsG@behat-test.com | DeputyDocsG |

  Scenario: Running the document-sync command syncs queued Report PDF documents with Sirius
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I run the document-sync command
    When I view the submissions page
    And I click on "tab-pending"
    Then I should see "12121212"
    And the report PDF document should be synced
    And the document "test-image.png" should be queued

  Scenario: Running the document-sync command syncs queued supporting documents with Sirius when the related report PDF document has been synced
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I run the document-sync command
    When I view the submissions page
    And I click on "tab-archived"
    Then I should see "12121212"
    And the document "test-image.png" should be synced
