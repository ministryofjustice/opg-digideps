Feature: Users can access the correct clients

  @prof
  Scenario: Team User cannot see clients belonging to inactive organisations
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    # Given x-client is in x-org and I log into x-org I should see x-client`
    And I should see the "client-01000010" region
    And I should see the "client-01000011" region
    And I should see the "client-01000012" region
    And I should see the "client-01000013" region
    # Given x-client is NOT in x-org and I log into x-org I should NOT see x-client`
    And I should not see the "client-02000001" region
    And I should not see the "client-02000002" region
    And I should not see the "client-02000003" region
    And I should not see the "client-03000001" region
    When I go to "/org/?limit=50"
    Then I should see the "client" region exactly 17 times

  @prof
  Scenario: Users can access correct clients reports
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    Then I click on "pa-report-open" in the "client-01000010" region
    And I save the report as "01000010-report"
    Then the response status code should be 200
    Then I go to "/logout"
    Then I am logged in as "behat-prof2@publicguardian.gov.uk" with password "Abcd1234"
    Then I go to "/org/?limit=50"
    Then I click on "pa-report-open" in the "client-02000001" region
    Then the response status code should be 200
    And I save the report as "02000001-report"
    When I go to the report URL "overview" for "01000010 report"
    Then the response status code should be 500
    Then I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    When I go to the report URL "overview" for "02000001-report"
    Then the response status code should be 500


  @prof
  Scenario: Organisation activated should not permit visibility other org clients
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I go to admin page "/admin/organisations"
    When I click on "edit" in the "org-behat-prof1publicguardiangovuk" region
    And I fill in "organisation_isActivated_0" with "1"
    And I press "Save organisation"
    # Given x-client is in x-org and I log into x-org I should see x-client`
    Then I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    And I should see the "client-01000010" region
    And I should see the "client-01000011" region
    And I should see the "client-01000012" region
    And I should see the "client-01000013" region
    # `given x-client is NOT in x-org and I log into x-org I should NOT see x-client`
    And I should not see the "client-02000001" region
    And I should not see the "client-02000002" region
    And I should not see the "client-02000003" region
    And I should not see the "client-03000001" region
    # Activating org should not change the client count
    When I go to "/org/?limit=50"
    Then I should see the "client" region exactly 17 times
    # try to access client 02000001 report
    When I go to "report/28/overview"
    Then the response status code should be 500

  @prof
  Scenario: Clients not belonging to org should not be visible
    # User logs in to org 2
    Given I am logged in as "behat-prof-admin@publicguardian.gov.uk" with password "Abcd1234"
    # Existing clients from org
    And I should see the "client-102-4-5" region
    And I should see the "client-102-5" region
    # `given x-client is in x-org and I log into y-org I should NOT see x-client`
    And I should not see the "client-02000001" region
    And I should not see the "client-01000010" region
    When I go to "/org/?limit=50"
    Then I should see the "client" region exactly 5 times

  @prof
  Scenario: Users from Org A should not see clients from Org B
    Given I am logged in as "behat-prof-admin@publicguardian.gov.uk" with password "Abcd1234"
    # Existing clients from org
    And I should see the "client-102-4-5" region
    And I should see the "client-102-5" region
    Then I click on "pa-report-open" in the "client-102-5" region
    Then the response status code should be 200
    When I go to "/org/?limit=50"
    # `given x-client is in x-org and I log into y-org I should NOT see x-client`
    And I should not see the "client-02000001" region
    And I should not see the "client-01000010" region
    And I should see the "client" region exactly 5 times
    When I go to "report/28/overview"
    Then the response status code should be 500

  @prof
  Scenario: Removing team member entries should seemlessly work
    Given I remove all the old team database entries
    # Given x-client is in x-org and I log into x-org I should see x-client`
    Then I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    And I should see the "client-01000010" region
    And I should see the "client-01000011" region
    And I should see the "client-01000012" region
    And I should see the "client-01000013" region
    # Given x-client is NOT in x-org and I log into x-org I should NOT see x-client`
    And I should not see the "client-02000001" region
    And I should not see the "client-02000002" region
    And I should not see the "client-02000003" region
    And I should not see the "client-03000001" region
    Then I click on "pa-report-open" in the "client-01000010" region
    Then the response status code should be 200
    When I go to "/org/?limit=50"
    Then I should see the "client" region exactly 17 times
    # try to access client 02000001 report
    When I go to "report/28/overview"
    Then the response status code should be 500

