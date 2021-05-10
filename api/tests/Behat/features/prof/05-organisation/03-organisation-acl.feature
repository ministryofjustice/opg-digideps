Feature: Users can access the correct clients

  Scenario: New client is added to existing deputy and brand new organisation added
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    # upload new Prof client 50000051 attached to org
    When I go to admin page "/admin/org-csv-upload"
    And I attach the file "behat-prof-new-clients.csv" to "admin_upload_file"
    And I press "admin_upload_upload"
    Then the form should be valid

  Scenario: Team User cannot see clients belonging to inactive organisations
    # log in as deputy should not see new client until org is activated
    Given I am logged in as "existing-deputy1@abc-solicitors.uk" with password "DigidepsPass1234"
    And I go to "/org/?limit=50"
    And I should see the "client-50000050" region
    And I should not see the "client-50000051" region

  Scenario: Organisation activated should not permit visibility of new clients belonging to org
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I go to admin page "/admin/organisations"
    When I click on "edit" in the "org-abc-solicitors" region
    And I fill in "organisation_isActivated_0" with "1"
    And I press "Save organisation"
    And I am logged in as "existing-deputy1@abc-solicitors.uk" with password "DigidepsPass1234"
    And I should see the "client-50000050" region
    And I should not see the "client-50000051" region

  Scenario: Team user added to existing org should enable visibility of new client
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I go to admin page "/admin/organisations"
    And I follow "ABC Solicitors"
    #And I create a new "NDR-disabled" "Prof Named" user "ABC org" "Administrator" with email "behat-pa-org1@pa-org1.gov.uk" and postcode "SW1"
    And I follow "Add user"
    And I fill in "organisation_add_user_email" with "existing-deputy1@abc-solicitors.uk"
    And I press "Find user"
    And I press "Add user to organisation"

  Scenario: Active organisation permits visibility of new client
    # log in as deputy should see new client
    Given I am logged in as "existing-deputy1@abc-solicitors.uk" with password "DigidepsPass1234"
    And I go to "/org/?limit=50"
    And I should see the "client-50000050" region
    And I should see the "client-50000051" region
    And I should see the "client" region exactly 2 times
    Then I click on "pa-report-open" in the "client-50000051" region
    Then the response status code should be 200

  Scenario: User not in an organisation attempting to access their client who is in an inactive organisation
    Given the organisation "org-1.co.uk" is inactive
    And "behat-prof-org-1@org-1.co.uk" has been removed from their organisation
    When I am logged in as "behat-prof-org-1@org-1.co.uk" with password "DigidepsPass1234"
    Then I should see the "client-03000025" region
    And I should not see the "client-03000026" region
    And I should see the "client" region exactly 1 times
    When I click on "pa-report-open" in the "client-03000025" region
    And I save the report as "03000025-report"
    Then the response status code should be 200

  Scenario: User in an inactive organisation attempting to access their client who is in an inactive organisation
    Given the organisation "org-1.co.uk" is inactive
    And "behat-prof-org-1@org-1.co.uk" has been added to the "org-1.co.uk" organisation
    When I am logged in as "behat-prof-org-1@org-1.co.uk" with password "DigidepsPass1234"
    Then I should see the "client-03000025" region
    And I should not see the "client-03000026" region
    And I should see the "client" region exactly 1 times
    When I click on "pa-report-open" in the "client-03000025" region
    Then the response status code should be 200

  Scenario: User attempting to view report not belonging to their client
    Given I am logged in as "behat-prof-org-2@org-1.co.uk" with password "DigidepsPass1234"
    And I click on "pa-report-open" in the "client-03000026" region
    And I save the report as "03000026-report"
    When I am logged in as "behat-prof-org-1@org-1.co.uk" with password "DigidepsPass1234"
    And I go to the report URL "overview" for "03000026-report"
    Then the response status code should be 404

  Scenario: User not in an organisation attempting to access their client who is in an active organisation
    Given the organisation "org-1.co.uk" is active
    And "behat-prof-org-1@org-1.co.uk" has been removed from their organisation
    When I am logged in as "behat-prof-org-1@org-1.co.uk" with password "DigidepsPass1234"
    And I go to the report URL "overview" for "03000025-report"
    And the response status code should be 404

  Scenario: User in an active organisation attempting to access clients inside and outside of the organisation
    Given the organisation "org-1.co.uk" is active
    And "behat-prof-org-1@org-1.co.uk" has been added to the "org-1.co.uk" organisation
    When I am logged in as "behat-prof-org-1@org-1.co.uk" with password "DigidepsPass1234"
    Then I should see the "client-03000025" region
    And I should see the "client-03000026" region
    And I should not see the "client-03000027" region
    And I should not see the "client-03000028" region
    When I click on "pa-report-open" in the "client-03000025" region
    Then the response status code should be 200
