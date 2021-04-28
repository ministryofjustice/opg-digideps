Feature: Deputy search
  @admin @admin-search @deputy-search
  Scenario: Search broadly across all deputy roles, excluding clients
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I search in admin for a deputy with the term "User-2"
    Then I should see "User-2"
    And I should see "Found 1 users"

  @admin @admin-search @deputy-search
  Scenario: Search broadly across deputy by a role, excluding clients
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I search in admin for a deputy with the term "Manager1" and filter role by "Public Authority deputies (named)"
    Then I should see "Found 0 users"
    When I search in admin for a deputy with the term "Manager1" and filter role by "Admin"
    And I press "admin_search"
    Then I should see "Found 1 users"

  @admin @admin-search @deputy-search
  Scenario: Search broadly across all deputy roles, including clients
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I search in admin for a deputy with the term "103-client"
    Then I should see "Found 0 users"
    And I should not see "LAY Deputy 103 User"
    When I search in admin for a deputy with the term "103-client" and include clients
    Then I should see "Found 1 users"
    And I should see "LAY Deputy 103 User"

  @admin@admin-search  @deputy-search
  Scenario: Search broadly across deputy by a role, including clients
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I search in admin for a deputy with the term "103-6-client" and filter role by "Public Authority deputies (named)" and include clients
    Then I should see "Found 1 users"

  @admin @admin-search @deputy-search
  Scenario: Search exact name match across all deputy roles, excluding clients
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I search in admin for a deputy with the term "Admin User"
    Then I should see "admin user"
    And I should see "Found 1 users"

  @admin @admin-search @deputy-search
  Scenario: Search exact name match across all deputy roles, including clients
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I search in admin for a deputy with the term "john 104-client"
    Then I should see "Found 0 users"
    When I search in admin for a deputy with the term "john 104-client" and include clients
    Then I should see "Found 1 users"
    And I should see "Lay Deputy 104 User"

  @admin @admin-search @deputy-search
  Scenario: Search exact name match across a specific deputy role
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I search in admin for a deputy with the term "Admin User" and filter role by "Public Authority deputies (named)"
    Then I should see "Found 0 users"
    When I search in admin for a deputy with the term "Admin User" and filter role by "Admin"
    Then I should see "Found 1 users"
    And I should see "Admin User"
