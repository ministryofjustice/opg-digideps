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
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    When I go to admin page "/admin/organisations"
    And I follow "abc-solicitors.uk"
    #And I create a new "NDR-disabled" "Prof Named" user "ABC org" "Administrator" with email "behat-pa-org1@pa-org1.gov.uk" and postcode "SW1"
    And I follow "Add someone to this organisation"
    And I fill in "organisation_add_user_email" with "existing-deputy1@abc-solicitors.uk"
    And I press "Find user"
    And I press "Add user to organisation"

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
  Scenario: Removing team member entries should seemlessly work
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    When I go to "/org/?limit=50"
    Then I should see the "client" region exactly 17 times
    When I remove all the old team database entries
    When I go to "/org/?limit=50"
    Then I should see the "client" region exactly 17 times
    Then I click on "pa-report-open" in the "client-31000010" region
    And I click on "client-edit"
    Then the response status code should be 200

  Scenario: User in a non active organisation can only see their own Clients
    Given I am logged in as "behat-prof-org-1@org-1.co.uk" with password "Abcd1234"
    And I should see the "client-03000025" region
    And I should not see the "client-03000026" region
    And I should see the "client" region exactly 1 times
    When I click on "pa-report-open" in the "client-03000025" region
    And I save the report as "client-03000025-report"
    Then the response status code should be 200

  Scenario: User in an inactive organisation edits a report
    Given I am logged in as "behat-prof-org-2@org-1.co.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-03000026" region
    And I save the report as "client-03000026-report"
    Then the response status code should be 200

  Scenario: User attempts to view report not belonging to their client
    Given I am logged in as "behat-prof-org-1@org-1.co.uk" with password "Abcd1234"
    When I go to the report URL "overview" for "client-03000026-report"
    Then the response status code should be 500

  Scenario: User in an active organisation can only see the organisations Clients
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I go to admin page "/admin/organisations"
    When I click on "edit" in the "org-behat-prof-org-3org-2couk" region
    And I fill in "organisation_isActivated_0" with "1"
    And I press "Save organisation"
    And I follow "behat-prof-org-3@org-2.co.uk"
    And I follow "Add someone to this organisation"
    And I fill in "organisation_add_user_email" with "behat-prof-org-1@org-1.co.uk"
    And I press "Find user"
    And I press "Add user to organisation"
    When I am logged in as "behat-prof-org-1@org-1.co.uk" with password "Abcd1234"
    Then I should not see the "client-03000025" region
    And I should see the "client-03000027" region
    And I should see the "client-03000028" region
    When I click on "pa-report-open" in the "client-03000027" region
    And I save the report as "client-03000027-report"
    Then the response status code should be 200

