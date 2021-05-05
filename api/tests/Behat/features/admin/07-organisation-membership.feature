Feature: Organisation membership

  Scenario: Set up organisation fixture
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And the following users exist:
      | ndr | deputyType | firstName | lastName | email | postCode |
      | disabled | PROF | Main | ERZ Contact | main.contact@erz.example | HA4 |
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
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I am on admin page "/admin/organisations"
    And I follow "ERZ Solicitors"
    When I follow "Add user"
    And I fill in "organisation_add_user_email" with "main.contact@erz.example"
    And I press "Find user"
    Then I should see "Main ERZ contact"
    And I should see "main.contact@erz.example"
    And I should see "Main ERZ contact will be able to see and report on all clients in the organisation"
    When I press "Add user to organisation"
    Then the URL should match "admin/organisations/\d+"
    And I should see "Main ERZ contact"

  @admin
  Scenario: Admin cannot add non-registered users to an organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I am on admin page "/admin/organisations"
    And I follow "ERZ Solicitors"
    And I follow "Add user"
    When I fill in "organisation_add_user_email" with "incorrect@publicguardian.gov.uk"
    And I press "Find user"
    Then I should see "Could not find user with specified email address"

  @admin
  Scenario: Admin cannot add lay or admin users to an organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I am on admin page "/admin/organisations"
    And I follow "ERZ Solicitors"
    And I follow "Add user"
    When I fill in "organisation_add_user_email" with "behat-lay-deputy-102@publicguardian.gov.uk"
    And I press "Find user"
    Then I should see "User has unsuitable role to be in this organisation"

  @admin
  Scenario: Admin cannot add duplicate users to an organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I am on admin page "/admin/organisations"
    And I follow "ERZ Solicitors"
    And I follow "Add user"
    When I fill in "organisation_add_user_email" with "main.contact@erz.example"
    And I press "Find user"
    Then I should see "User is already in this organisation"
    When I fill in "organisation_add_user_email" with "Main.Contact@erz.example"
    And I press "Find user"
    Then I should see "User is already in this organisation"

  @admin
  Scenario: Public domains: Admin can add users from different domains
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And the following users exist:
      | ndr | deputyType | firstName | lastName | email | postCode |
      | disabled | PROF | Rana | Kossak | rana.kossak@example.com | HA4 |
    And I am on admin page "/admin/organisations"
    And I follow "john.smith@abc-solicitors.example.com"
    And I follow "Add user"
    When I fill in "organisation_add_user_email" with "rana.kossak@example.com"
    And I press "Find user"
    When I press "Add user to organisation"
    Then the URL should match "admin/organisations/\d+"
    And I should see "Rana Kossak"

  @admin
  Scenario: Public domains: Admin can add initial user to a public domain organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I am on admin page "/admin/organisations"
    And I follow "jo.brown@example.com"
    And I follow "Add user"
    When I fill in "organisation_add_user_email" with "jo.brown@example.com"
    And I press "Find user"
    Then I should see "PROF Deputy example1 User"
    And I should see "jo.brown@example.com"
    And I should see "PROF Deputy example1 User will be able to see and report on all clients in the organisation"
    When I press "Add user to organisation"
    Then the URL should match "admin/organisations/\d+"
    And I should see "PROF Deputy Example"

  @admin
  Scenario: Public domains: Admin can add additional users to a public domain organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I am on admin page "/admin/organisations"
    And I follow "jo.brown@example.com"
    And I follow "Add user"
    When I fill in "organisation_add_user_email" with "bobby.blue@example.com"
    And I press "Find user"
    When I press "Add user to organisation"
    Then the URL should match "admin/organisations/\d+"
    And I should see "bobby.blue@example.com"

  @admin
  Scenario: Admin can remove users from an organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I am on admin page "/admin/organisations"
    And I follow "ERZ Solicitors"
    When I click on "delete" in the "org-main-erz-contact" region
    Then I should see "Are you sure you want to remove this user from this organisation?"
    And I should see "ERZ Solicitors"
    And I should see "Main ERZ Contact"
    And I should see "main.contact@erz.example"
    When I press "Yes, remove user from this organisation"
    Then the URL should match "admin/organisations/\d+"
    And I should not see "PROF Deputy example1 User"
