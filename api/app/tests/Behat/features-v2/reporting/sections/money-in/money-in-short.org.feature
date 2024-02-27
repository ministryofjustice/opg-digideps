@v2 @v2_reporting_2 @money-in-low-assets @Iqpal
Feature: Money in Low Assets - Org users

    @prof-pfa-low-not-started
    Scenario: A user has had no money go in
        Given a Professional Admin has not started a Pfa Low Assets report
        And I visit the report overview page
        Then I should see "money-in-short" as "not started"
        When I view and start the money in short report section
        And I confirm "No" to adding money in on the clients behalf
        And I enter a reason for no money in short
        Then I should see the expected money in section summary
        When I follow link back to report overview page
        Then I should see "money-in-short" as "no money in"
