Feature: Limiting access to fixture endpoints to super admins
  As a super admin user
  In order to prevent other types of users from creating any data
  I need to limit access to fixture endpoints to other types of users

  Scenario: Super admin can access fixture endpoints in navigation bar
    Given I am logged in to admin as "super-admin@publicguardian.gov.uk" with password "Abcd1234"
    When I follow "Fixtures"
    Then I should be on "/admin/fixtures/list"
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I clear the cache
    Then I go to "/"
    Then I should not see "Fixtures"
