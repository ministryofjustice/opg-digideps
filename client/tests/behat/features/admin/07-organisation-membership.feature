Feature: Organisation membership

  @admin
  Scenario: Set up organisation fixture
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I am on admin page "/admin/organisations"
    When I follow "Add a new organisation"
    And I fill in "organisation_name" with "ERZ Solicitors"
    And I fill in "organisation_emailIdentifierType_0" with "domain"
    And I fill in "organisation_emailDomain" with "erz.example"
    And I fill in "organisation_isActivated_0" with "1"
    And I press "Save organisation"
    Then I should see the "org-erz-solicitors" region

  @admin
  Scenario: Admin can add members to an organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I am on admin page "/admin/organisations"
    And I follow "ERZ Solicitors"
    When I follow "Add someone to this organisation"
    And I fill in "organisation_add_user_email" with "behat-prof-deputy-102-5@publicguardian.gov.uk"
    And I press "Find user"
    Then I should see "PROF Deputy 102-5 User"
    And I should see "behat-prof-deputy-102-5@publicguardian.gov.uk"
    And I should see "PROF Deputy 102-5 User will be able to see and report on all clients in the organisation"
    When I press "Add user to organisation"
    Then the URL should match "admin/organisations/\d+"
    And I should see "PROF Deputy 102-5 User"

  @admin
  Scenario: Admin cannot add non-registered users to an organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I am on admin page "/admin/organisations"
    And I follow "ERZ Solicitors"
    And I follow "Add someone to this organisation"
    When I fill in "organisation_add_user_email" with "incorrect@publicguardian.gov.uk"
    And I press "Find user"
    Then I should see "Could not find user with specified email address"

  @admin
  Scenario: Admin cannot add lay or admin users to an organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I am on admin page "/admin/organisations"
    And I follow "ERZ Solicitors"
    And I follow "Add someone to this organisation"
    When I fill in "organisation_add_user_email" with "behat-lay-deputy-102@publicguardian.gov.uk"
    And I press "Find user"
    Then I should see "User has unsuitable role to be in this organisation"

  @admin
  Scenario: Admin cannot add duplicate users to an organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I am on admin page "/admin/organisations"
    And I follow "ERZ Solicitors"
    And I follow "Add someone to this organisation"
    When I fill in "organisation_add_user_email" with "behat-prof-deputy-102-5@publicguardian.gov.uk"
    And I press "Find user"
    Then I should see "User is already in this organisation"

  @admin
  Scenario: Admin cannot add users to a public domain organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I am on admin page "/admin/organisations"
    And I follow "jo.brown@example.com"
    And I follow "Add someone to this organisation"
    When I fill in "organisation_add_user_email" with "jo.brown@example.com"
    And I press "Find user"
    Then I should see "You cannot add a user to an organisation with a public domain"

  @admin
  Scenario: Admin cannot add users witth different email domain to that of the organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I am on admin page "/admin/organisations"
    And I follow "john.smith@abc-solicitors.example.com"
    And I follow "Add someone to this organisation"
    When I fill in "organisation_add_user_email" with "jo.brown@example.com"
    And I press "Find user"
    Then I should see "User does not have an email address from this organisation"

  @admin
  Scenario: Admin can remove users from an organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I am on admin page "/admin/organisations"
    And I follow "ERZ Solicitors"
    When I click on "delete" in the "org-prof-deputy-102-5-user" region
    Then I should see "Are you sure you want to remove this user from this organisation?"
    And I should see "ERZ Solicitors"
    And I should see "PROF Deputy 102-5 User"
    When I press "Yes, remove user from this organisation"
    Then the URL should match "admin/organisations/\d+"
    And I should not see "PROF Deputy 102-5 User"

  @admin
  Scenario: Users can be in more than one organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I am on admin page "/admin/organisations"
    And I follow "Add a new organisation"
    And I fill in "organisation_name" with "Mabey Street Lawyers"
    And I fill in "organisation_emailIdentifierType_0" with "address"
    And I fill in "organisation_emailAddress" with "mabeystreet@gmail.example"
    And I fill in "organisation_isActivated_0" with "1"
    And I press "Save organisation"
    When I follow "Mabey Street Lawyers"
    And I follow "Add someone to this organisation"
    And I fill in "organisation_add_user_email" with "behat-prof-deputy-102-5@publicguardian.gov.uk"
    And I press "Find user"
    And I press "Add user to organisation"
    Then the URL should match "admin/organisations/\d+"
    And I should see "PROF Deputy 102-5 User"
