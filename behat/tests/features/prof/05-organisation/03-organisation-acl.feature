Feature: Users can access the correct clients

  Scenario: User not in an organisation attempting to access their client who is in an inactive organisation
    Given the organisation "org-1.co.uk" is inactive
    And "behat-prof-org-1@org-1.co.uk" has been removed from their organisation
    When I am logged in as "behat-prof-org-1@org-1.co.uk" with password "Abcd1234"
    Then I should see the "client-03000025" region
    And I should not see the "client-03000026" region
    And I should see the "client" region exactly 1 times
    When I click on "pa-report-open" in the "client-03000025" region
    And I save the report as "03000025-report"
    Then the response status code should be 200

  Scenario: User in an inactive organisation attempting to access their client who is in an inactive organisation
    Given the organisation "org-1.co.uk" is inactive
    And "behat-prof-org-1@org-1.co.uk" has been added to the "org-1.co.uk" organisation
    When I am logged in as "behat-prof-org-1@org-1.co.uk" with password "Abcd1234"
    Then I should see the "client-03000025" region
    And I should not see the "client-03000026" region
    And I should see the "client" region exactly 1 times
    When I click on "pa-report-open" in the "client-03000025" region
    Then the response status code should be 200

  Scenario: User attempting to view report not belonging to their client
    Given I am logged in as "behat-prof-org-2@org-1.co.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-03000026" region
    And I save the report as "03000026-report"
    When I am logged in as "behat-prof-org-1@org-1.co.uk" with password "Abcd1234"
    And I go to the report URL "overview" for "03000026-report"
    Then the response status code should be 500

  Scenario: User not in an organisation attempting to access their client who is in an active organisation
    Given the organisation "org-1.co.uk" is active
    And "behat-prof-org-1@org-1.co.uk" has been removed from their organisation
    When I am logged in as "behat-prof-org-1@org-1.co.uk" with password "Abcd1234"
    And I go to the report URL "overview" for "03000025-report"
    And the response status code should be 500

  Scenario: User in an active organisation attempting to access clients inside and outside of the organisation
    Given the organisation "org-1.co.uk" is active
    And "behat-prof-org-1@org-1.co.uk" has been added to the "org-1.co.uk" organisation
    When I am logged in as "behat-prof-org-1@org-1.co.uk" with password "Abcd1234"
    Then I should see the "client-03000025" region
    And I should see the "client-03000026" region
    And I should not see the "client-03000027" region
    And I should not see the "client-03000028" region
    When I click on "pa-report-open" in the "client-03000025" region
    Then the response status code should be 200
