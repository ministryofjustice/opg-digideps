Feature: Deputy search
  @admin @deputy-search
  Scenario: Search across all deputy roles, excluding clients
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I fill in "admin_q" with "User-2"
    When I press "admin_search"
    Then I should see "User-2"
    And I should see "Found 1 users"

  @admin @deputy-search
  Scenario: Search across deputy by a role, excluding clients
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I fill in "admin_q" with "Manager1"
    And I select "ROLE_PA_NAMED" from "admin[role_name]"
    When I press "admin_search"
    Then I should see "Found 0 users"
    When I select "ROLE_CASE_MANAGER" from "admin[role_name]"
    And I press "admin_search"
    Then I should see "Found 1 users"

  @admin @deputy-search
  Scenario: Search across all deputy roles, including clients
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I fill in "admin_q" with "103-client"
    When I press "admin_search"
    Then I should see "Found 0 users"
    And I should not see "LAY Deputy 103 User"
    When I check "Include clients"
    And I press "admin_search"
    Then I should see "Found 1 users"
    And I should see "LAY Deputy 103 User"

  @admin @deputy-search
  Scenario: Search across deputy by a role, including clients
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I fill in "admin_q" with "103-6-client"
    And I check "Include clients"
    When I press "admin_search"
    Then I should see "Found 3 users"
    When I select "ROLE_PA_NAMED" from "admin[role_name]"
    And I press "admin_search"
    Then I should see "Found 1 users"
