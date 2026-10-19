@client_benefits_check @v2 @v2_reporting_1
Feature: Client benefits check - Org users (only overview pages differ - flow is identical for all user types)

    @pa-admin-combined-high-not-started
    Scenario: Reports due at least 60 days after the new question feature flag see the new report section
        Given a Public Authority Deputy has not started a Combined High Assets report
        When I visit the report overview page
        Then I should see "client-benefits-check" as "not started"
