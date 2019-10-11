Feature: Users can view their organisations

  @prof
  Scenario: Without organisation, user cannot access settings pages
    Given I am logged in as "behat-prof-admin@publicguardian.gov.uk" with password "Abcd1234"
    When I go to "/org/settings"
    And I follow "User accounts"
    Then I should be on "/org/settings/user-accounts"
    When I go to "/org/settings/organisation"
    Then the response status code should be 404

  @prof
  Scenario: Set up organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    When I go to admin page "/admin/organisations/add"
    And I fill in "organisation_name" with "Leever Partners"
    And I fill in "organisation_emailIdentifierType_0" with "domain"
    And I fill in "organisation_emailDomain" with "leever.example"
    And I fill in "organisation_isActivated_0" with "0"
    And I press "Save organisation"
    # Add user manually due to fixtures auto creating the organisation
    And I go to admin page "/admin"
    And I create a new "NDR-disabled" "prof named" user "Main" "Leever Contact" with email "main.contact@leever.example" and postcode "HA4"
    And emails are sent from "admin" area
    And I activate the named deputy with password "Abcd1234"
    When I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    When I go to admin page "/admin/organisations"
    And I follow "Leever Partners"
    And I follow "Add someone to this organisation"
    And I fill in "organisation_add_user_email" with "main.contact@leever.example"
    And I press "Find user"
    And I press "Add user to organisation"
    Then the URL should match "admin/organisations/\d+"
    And I should see "Leever Partners"
    And I should see "Main Leever Contact"

  @prof
  Scenario: When organisation is not active, user cannot access settings pages
    Given I am logged in as "main.contact@leever.example" with password "Abcd1234"
    When I go to "/org/settings"
    And I follow "User accounts"
    Then I should be on "/org/settings/user-accounts"
    When I go to "/org/settings/organisation"
    Then the response status code should be 404

  @prof
  Scenario: Activate organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I go to admin page "/admin/organisations"
    When I click on "edit" in the "org-leever-partners" region
    And I fill in "organisation_isActivated_0" with "1"
    And I press "Save organisation"

  @prof
  Scenario: User can view their active organisation
    Given I am logged in as "main.contact@leever.example" with password "Abcd1234"
    When I go to "/org/settings"
    And I follow "User accounts"
    Then the URL should match "/org/settings/organisation/\d+"
    And the response status code should be 200
    And I should see "Leever Partners"
    And I should see "Main org contact"

  @prof
  Scenario: Clean up additional organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I am on admin page "/admin/organisations"
    When I click on "delete" in the "org-stears-and-co" region
    And I click on "confirm"
    Then I should not see "Stears and Co."
