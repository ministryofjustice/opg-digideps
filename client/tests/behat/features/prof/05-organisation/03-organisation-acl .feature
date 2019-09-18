Feature: Users can tthe correct clients

  @prof
  Scenario: Clients not in Organisation users cannot see clients belonging to inactive organisations
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
  # Existing clients from team membership
    And I should see the "client-01000010" region
    And I should see the "client-01000011" region
    And I should see the "client-01000012" region
    And I should see the "client-01000013" region
  # Existing clients from org membership
    And I should not see the "client-02000001" region
    And I should not see the "client-02000002" region
    And I should not see the "client-02000003" region
    And I should not see the "client-03000001" region

  @prof
  Scenario: Organisation activated should permit visibility of team and org clients
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I go to admin page "/admin/organisations"
    When I click on "edit" in the "org-behat-prof1publicguardiangovuk" region
    And I fill in "organisation_isActivated_0" with "1"
    And I press "Save organisation"
    Then I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    # Existing clients from team membership
    # Given x-client is in x-org and I log into x-org I should see x-client`
    And I should see the "client-01000010" region
    And I should see the "client-01000011" region
    And I should see the "client-01000012" region
    And I should see the "client-01000013" region
    # New clients from org membership
    And I should see the "client-02000001" region
    And I should see the "client-02000002" region
    And I should see the "client-02000003" region
    And I should see the "client-03000001" region

  @prof
  Scenario: Clients not belonging to org should not be visible
    # User logs in to org 2
    Given I am logged in as "behat-prof-admin@publicguardian.gov.uk" with password "Abcd1234"
    # Existing clients from org 1
    # `given x-client is in x-org and I log into y-org I should NOT see x-client`
    And I should not see the "client-01000010" region
    And I should not see the "client-01000011" region
    And I should not see the "client-01000012" region
    And I should not see the "client-01000013" region


