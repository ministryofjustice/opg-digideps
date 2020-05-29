Feature: Limiting access to fixture endpoints to super admins
  As a super admin user
  In order to prevent other types of users from creating any data
  I need to limit access to fixture endpoints to other types of users

  @test
  Scenario: Super admin can access fixture endpoints in navigation bar
    Given I am logged in as "super-admin@publicguardian.gov.uk" with password "Abcd1234"
    When I follow "Fixtures"
    Then I should be on "/fixtures"
