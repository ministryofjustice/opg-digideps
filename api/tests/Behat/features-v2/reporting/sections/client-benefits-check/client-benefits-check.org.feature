@client_benefits_check @v2 @v2_reporting_1
Feature: Client benefits check - Org users (only overview pages differ - flow is identical for all user types)

    @prof-admin-combined-high-completed
    Scenario: Reports due before the new question feature flag do not see the new report section and can submit report
        Given a Professional Admin Deputy has completed a Combined High Assets report
        But they have not completed the client benefits section for their 'current' report
        And the deputies 'current' report ends and is due 'less' than 60 days after the client benefits check feature flag date
        When I visit the report overview page
        Then I should not see 'client-benefits-check' report section
        And I should be able to submit my report without completing the client benefits check section

    @pa-admin-combined-high-not-started
    Scenario: Reports due at least 60 days after the new question feature flag see the new report section
        Given a Public Authority Deputy has not started a Combined High Assets report
        But they have not completed the client benefits section for their 'current' report
        And the deputies 'current' report ends and is due 'more' than 60 days after the client benefits check feature flag date
        When I visit the report overview page
        Then I should see "client-benefits-check" as "not started"
