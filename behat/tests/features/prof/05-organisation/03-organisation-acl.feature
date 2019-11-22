Feature: Users can access the correct clients

  @prof @prof-report-acl
  Scenario: User in an active organisation can only see the organisations Clients
    Given "behat-prof-org-1@org-1.co.uk" has been added to the "org-1.co.uk" organisation
    And the organisation "org-1.co.uk" is active
    And I am logged in as "behat-prof-org-1@org-1.co.uk" with password "Abcd1234"
    Then I should see "Showing 2 clients"
    And I should see the "client-03000026" region
    And I should see the "client-03000025" region
    When I click on "pa-report-open" in the "client-03000026" region
    Then the response status code should be 200
    And I save the report as "client-03000026-report"

  @prof @prof-report-acl
  Scenario: User in an inactive Organisation can not access any Clients or Reports
    Given "behat-prof-org-1@org-1.co.uk" has been added to the "org-1.co.uk" organisation
    And the organisation "org-1.co.uk" is inactive
    And I am logged in as "behat-prof-org-1@org-1.co.uk" with password "Abcd1234"
    Then I should see "No reports found"
    When I go to the report URL "overview" for "client-03000026-report"
    Then the response status code should be 500

  @prof @prof-report-acl
  Scenario: User not in an Organisation can not access any Clients or Reports
    Given "behat-prof-org-1@org-1.co.uk" has been removed from their organisation
    And the organisation "org-1.co.uk" is active
    When I am logged in as "behat-prof-org-1@org-1.co.uk" with password "Abcd1234"
    Then I should see "No reports found"
    When I go to the report URL "overview" for "client-03000026-report"
    Then the response status code should be 500
