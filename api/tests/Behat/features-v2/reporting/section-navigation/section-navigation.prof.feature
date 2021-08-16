@v2 @section-navigation
Feature: Section navigation - Professional

    @lay-combined-high-not-started
    Scenario: Deputy Costs
        Given a Lay Deputy has not started a Combined High Assets report
        When I visit the accounts report section
        Then the previous section should be "xxx"
        And the next section should be "xxx"

    @lay-combined-high-not-started
    Scenario: Deputy Cost Estimates
        Given a Lay Deputy has not started a Combined High Assets report
        When I visit the accounts report section
        Then the previous section should be "xxx"
        And the next section should be "xxx"
