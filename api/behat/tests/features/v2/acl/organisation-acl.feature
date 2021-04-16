Feature: Managing client and report access for deputies within active organisations
  In order to ensure that sufficient data protection is in place for clients
  As a system
  I need to control the access that is granted for each client and their reports

  Scenario: Create court orders for the feature
    Given the following court orders exist:
      | client   | deputy     | deputy_type | report_type                                | court_date |
      | 95432265 | Deputy1943 | PROF        | Property and Financial Affairs High Assets | 2018-01-30 |
      | 94235342 | Deputy5492 | PROF        | Property and Financial Affairs High Assets | 2018-01-30 |
      | 54234341 | Deputy8543 | PROF        | Property and Financial Affairs High Assets | 2018-01-30 |

  Scenario: A deputy can only view clients that belong to their sole organisation
    Given I am logged in as "deputy8543@behat-test.com" with password "DigidepsPass1234"
    Then I should see "54234341"
    And I should not see "Org 8543 Ltd"
    And I should not see "95432265"
    And I should not see "94235342"

  Scenario: A deputy can only view clients that belong to their multiple organisations
    Given "deputy1943@behat-test.com" has been added to the "deputy5492@behat-test.com" organisation
    When I am logged in as "deputy1943@behat-test.com" with password "DigidepsPass1234"
    Then I should see "95432265"
    And I should see "Org 1943 Ltd"
    And I should see "94235342"
    And I should see "Org 5492 Ltd"
    And I should not see "54234341"

  Scenario: A deputy can only access reports that belong to clients within the deputy's organisations
    When I am logged in as "deputy1943@behat-test.com" with password "DigidepsPass1234"
    And I follow "95432265-Client, John"
    Then I should see "Client profile"
    When "deputy1943@behat-test.com" has been removed from the "deputy1943@behat-test.com" organisation
    And I reload the page
    Then I should see "Page not found"
    When I follow "Dashboard"
    And I follow "94235342-Client, John"
    Then I should see "Client profile"
