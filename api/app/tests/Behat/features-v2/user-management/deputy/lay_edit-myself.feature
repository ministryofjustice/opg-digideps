@v2
Feature: Deputy accesses their deputyship details

    @lay-health-welfare-not-started
    Scenario: Deputy accesses their deputyship details
        Given a Lay Deputy exists
        Given I view the lay deputy your details page
        Then I should not see the link for clients details
