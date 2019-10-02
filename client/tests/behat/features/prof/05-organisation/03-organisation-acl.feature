Feature: Users can access the correct clients

  @prof
    # fixture data inserts existing deputty attached to a single client
  Scenario: Existing Team User can still access clients belonging to their team
    Given I am logged in as "existing-deputy1@abc-solicitors.uk" with password "Abcd1234"
    And I go to "/org/?limit=50"
    And I should see the "client" region exactly 1 times
    And I should see the "client-50000050" region
    Then I click on "pa-report-open" in the "client-50000050" region
    And I save the report as "50000050-report"
    Then the response status code should be 200

  @prof
  Scenario: New client is added to existing deputy and brand new organisation added
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    # upload new Prof client 50000051 attached to org
    When I click on "admin-upload-pa"
    And I attach the file "behat-prof-new-clients.csv" to "admin_upload_file"
    And I press "admin_upload_upload"
    Then the form should be valid

  @prof
  Scenario: Team User cannot see clients belonging to inactive organisations
    # log in as deputy should not see new client until org is activated
    Given I am logged in as "existing-deputy1@abc-solicitors.uk" with password "Abcd1234"
    And I go to "/org/?limit=50"
    And I should see the "client-50000050" region
    And I should not see the "client-50000051" region

  @prof
  Scenario: Organisation activated should not permit visibility of new clients belonging to org
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I go to admin page "/admin/organisations"
    When I click on "edit" in the "org-existing-deputy1abc-solicitorsuk" region
    And I fill in "organisation_isActivated_0" with "1"
    And I press "Save organisation"
    And I am logged in as "existing-deputy1@abc-solicitors.uk" with password "Abcd1234"
    And I should see the "client-50000050" region
    And I should not see the "client-50000051" region

  @prof
  Scenario: Team user added to existing org should enable visibility of new client
    Given I am logged in as "behat-prof-admin@publicguardian.gov.uk" with password "Abcd1234"
    And emails are sent from "deputy" area
    When I go to "/org/settings/organisation"
    And I follow "Add user"
    And I press "Save"
    Then the form should be invalid
    When I fill in the following:
      | organisation_member_email     | existing-deputy1@abc-solicitors.uk |
    And I press "Save"
    And the last email should have been sent to "existing-deputy1@abc-solicitors.uk"
    And the last email should contain "Activate your account"
    And the last email should contain "/user/activate"

  @prof
  Scenario: Active organisation permits visibility of new client
    # log in as deputy should see new client
    Given I am logged in as "existing-deputy1@abc-solicitors.uk" with password "Abcd1234"
    And I go to "/org/?limit=50"
    And I should see the "client-50000050" region
    And I should see the "client-50000051" region
    And I should see the "client" region exactly 2 times
    Then I click on "pa-report-open" in the "client-50000051" region
    Then the response status code should be 200

  @prof
  Scenario: Users from Org A should not see clients from Org B
#    Given I am logged in as "behat-prof-admin@publicguardian.gov.uk" with password "Abcd1234"
#    # Existing clients from org
#    And I should see the "client-102-5" region
#    And I should see the "client-103-5" region
#    And I should see the "client-104-5" region
#    And I should see the "client-102-4-5" region
#    And I should see the "client-103-4-5" region
#    And I should see the "client" region exactly 5 times
#    Then I click on "pa-report-open" in the "client-102-5" region
#    Then the response status code should be 200
#    When I go to "/org/?limit=50"
#    # `given x-client is in x-org and I log into y-org I should NOT see x-client`
#    And I should not see the "client-02000001" region
#    And I should not see the "client-01000010" region
#    When I go to the report URL "overview" for "32000001-report"
#    And the response status code should be 500

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
    When I go to the report URL "overview" for "32000001-report"
    And the response status code should be 500
