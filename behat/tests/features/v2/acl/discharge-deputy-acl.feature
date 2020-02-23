Feature: Managing access to the deputy discharge feature

  Scenario: Create court orders for the feature
    Given the following court orders exist:
      | client   | deputy     | deputy_type | report_type                                | court_date |
      | 74853721 | Deputy9584 | PROF        | Property and Financial Affairs High Assets | 2018-01-30 |

  Scenario: Super admin can discharge deputies from their clients
    Given I am logged in as "admin@publicguardian.gov.uk" with password "Abcd1234"
