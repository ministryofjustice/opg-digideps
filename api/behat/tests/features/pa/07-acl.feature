Feature: PA cannot access other's PA's reports and clients
# team1 = team with client 2100010
# team2 = team with client 2000003
  Scenario: CSV org-upload
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    # upload PA users
    When I go to admin page "/admin/org-csv-upload"
    And I attach the file "behat-pa-orgs.csv" to "admin_upload_file"
    And I press "admin_upload_upload"
    Then the form should be valid

  Scenario: Admin activates PA Org 1 deputy
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And the following users exist:
      | ndr | deputyType | firstName | lastName | email | postCode | activated |
      | disabled | PA | Org1 Case | Worker | behat-pa-org1@pa-org1.gov.uk | SW1 | true |
    # simulate existing deputies with clients by adding entry to deputy_case table
    And I add the client with case number "40000041" to be deputised by email "behat-pa-org1@pa-org1.gov.uk"

  Scenario: Admin activates PA Org 2 deputy
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And the following users exist:
      | ndr | deputyType | firstName | lastName | email | postCode | activated |
      | disabled | PA | Org2 Case | Worker | behat-pa-org2@pa-org2.gov.uk | SW1 | true |
    # simulate existing deputies with clients by adding entry to deputy_case table
    And I add the client with case number "40000042" to be deputised by email "behat-pa-org2@pa-org2.gov.uk"

  Scenario: PA Org 1 can access own reports and clients
    Given I am logged in as "behat-pa-org1@pa-org1.gov.uk" with password "DigidepsPass1234"
    # access report and save for future feature tests
    Then I click on "pa-report-open" in the "client-40000041" region
    And I save the report as "40000041-report"
    And I click on "client-edit"
    And the response status code should be 200
    And I save the current URL as "client-40000041-edit"
    Then I go to "/logout"

  Scenario: PA Org 2 can access own reports and clients
    Given I am logged in as "behat-pa-org2@pa-org2.gov.uk" with password "DigidepsPass1234"
    Then I click on "pa-report-open" in the "client-40000042" region
    And I save the report as "40000042-report"
    And I click on "client-edit"
    And the response status code should be 200
    And I save the current URL as "client-40000042-edit"
    Then I go to "/logout"

  Scenario: PA Org 1 user logs in and should only see their clients and reports (from the existing team structure)
    Given I am logged in as "behat-pa-org1@pa-org1.gov.uk" with password "DigidepsPass1234"
    And I should see the "client-40000041" region
    And I should not see the "client-40000042" region
    Then I go to the report URL "overview" for "40000042-report"
    And the response status code should be 404
    Then I go to the URL previously saved as "client-40000042-edit"
    And the response status code should be 404

  Scenario: User in an active organisation attempting to access clients inside and outside of the organisation
    Given the organisation "pa-org1.gov.uk" is active
    And "behat-pa-org1@pa-org1.gov.uk" has been added to the "pa-org1.gov.uk" organisation
    When I am logged in as "behat-pa-org1@pa-org1.gov.uk" with password "DigidepsPass1234"
    Then I should see the "client-40000041" region
    And I should not see the "client-40000042" region
    Then I go to the report URL "overview" for "40000041-report"
    And the response status code should be 200
    Then I go to the report URL "overview" for "40000042-report"
    And the response status code should be 404

  Scenario: User not in an organisation attempting to access their client who is in an active organisation
    Given the organisation "pa-org1.gov.uk" is active
    And "behat-pa-org1@pa-org1.gov.uk" has been removed from their organisation
    When I am logged in as "behat-pa-org1@pa-org1.gov.uk" with password "DigidepsPass1234"
    And I go to the report URL "overview" for "40000041-report"
    And the response status code should be 404

  Scenario: User not in an organisation attempting to access their client who is in an inactive organisation
    Given the organisation "pa-org1.gov.uk" is inactive
    And "behat-pa-org1@pa-org1.gov.uk" has been removed from their organisation
    When I am logged in as "behat-pa-org1@pa-org1.gov.uk" with password "DigidepsPass1234"
    And I go to the report URL "overview" for "40000041-report"
    And the response status code should be 200
