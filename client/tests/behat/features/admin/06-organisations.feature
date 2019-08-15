Feature: Administration of organisations

#   @admin
#   Scenario: Navbar works
#     Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
#     Then I should see "Organisations" in the "navbar" region
#     When I click on "admin-organisations" in the "navbar" region
#     Then I should be on "/admin/organisations/"

  @admin
  Scenario: Admin can create organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I am on admin page "/admin/organisations"
    When I follow "Add a new organisation"
    Then I should be on "/admin/organisations/add"
    # Check fields are required
    When I press "Save organisation"
    Then I should see "Please enter an organisation name"
    Then I should see "Please select an email identifier"
    Then I should see "Please select whether the organisation should be activated"
    # Fill in proper details
    When I fill in "organisation_name" with "Domain-owning organisation"
    And I fill in "organisation_emailIdentifierType_0" with "domain"
    And I fill in "organisation_emailDomain" with "example.com"
    And I fill in "organisation_isActivated_0" with "1"
    And I press "Save organisation"
    Then I should be on "/admin/organisations/"
    And each text should be present in the corresponding region:
        | Domain-owning organisation | org-domain-owning-organisation |
        | *@example.com              | org-domain-owning-organisation |
        | Active                     | org-domain-owning-organisation |

  @admin
  Scenario: Admin can choose email identifier when creating an organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I am on admin page "/admin/organisations/add"
    # Check domain value is not carried across
    When I fill in "organisation_emailIdentifierType_0" with "domain"
    And I fill in "organisation_emailDomain" with "test.com"
    And I fill in "organisation_emailIdentifierType_0" with "address"
    Then the "organisation_emailAddress" field should contain ""
    # Check submission of an email address identifier works
    When I fill in "organisation_emailAddress" with "test@gmail.com"
    And I fill in "organisation_name" with "Email address-owning organisation"
    And I fill in "organisation_isActivated_0" with "0"
    And I press "Save organisation"
    Then I should be on "/admin/organisations/"
    And each text should be present in the corresponding region:
        | Email address-owning organisation | org-email-address-owning-organisation |
        | test@gmail.com                    | org-email-address-owning-organisation |
    And I should not see "Active" in the "org-email-address-owning-organisation" region

  @admin
  Scenario: API errors are reported back to user
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I am on admin page "/admin/organisations/add"
    And I fill in "organisation_name" with "Duplicate organisation"
    And I fill in "organisation_isActivated_0" with "0"
    When I fill in "organisation_emailIdentifierType_0" with "domain"
    And I fill in "organisation_emailDomain" with "example.com"
    And I press "Save organisation"
    Then I should see "Email identifer already in use"

  @admin
  Scenario: Admin can edit an organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I am on admin page "/admin/organisations"
    When I click on "edit" in the "org-email-address-owning-organisation" region
    # Data should be prefilled
    Then I should see "Email address-owning organisation"
    And the "organisation_name" field should contain "Email address-owning organisation"
    And the "organisation_emailIdentifierType_0" field should contain "address"
    And the "organisation_emailAddress" field should contain "test@gmail.com"
    And the "organisation_emailDomain" field should contain ""
    And the "organisation_isActivated_0" field should contain "0"
    # Data can be changed
    When I fill in "organisation_name" with "SomeSolicitors.org"
    And I fill in "organisation_emailIdentifierType_0" with "domain"
    And I fill in "organisation_emailDomain" with "somesolicitors.org"
    And I fill in "organisation_isActivated_0" with "1"
    And I press "Save organisation"
    Then each text should be present in the corresponding region:
        | SomeSolicitors.org   | org-somesolicitorsorg |
        | *@somesolicitors.org | org-somesolicitorsorg |
        | Active               | org-somesolicitorsorg |

  @admin
  Scenario: Admin can delete an organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I am on admin page "/admin/organisations"
    When I click on "delete" in the "org-somesolicitorsorg" region
    Then I should see "Are you sure you want to remove this organisation?"
    And I should see "SomeSolicitors.org"
    And I should see "*@somesolicitors.org"
    When I click on "confirm"
    Then I should not see the "org-somesolicitorsorg" region
    And I should see the "org-domain-owning-organisation" region
