@v2 @deputy-costs
Feature: Deputy costs - Applies to Org users only

    @prof-admin-not-started
    Scenario: A professional deputy has fixed deputy costs only
        Given a Professional Admin Deputy has not started a report
        When I navigate to and start the deputy costs report section for an existing client
        And I have fixed deputy costs to declare
        And my client has not paid me in the current reporting period for work from a previous period
        And I enter a valid amount for the current reporting period costs
        And I have no additional costs to declare for the current reporting period
        Then I should see the expected responses on the deputy costs summary page

    @prof-admin-not-started @acs2
    Scenario: A professional deputy has assessed deputy costs only
        Given a Professional Admin Deputy has not started a report
        When I visit and start the deputy costs report section for an existing client
        And I have assessed deputy costs to declare
        And my client has not paid me in the current reporting period for work from a previous period
        And I have charged in line with interim billing under Practice Direction 19B
        And I have provided valid interim costs and dates for all three periods
        And I enter a valid amount and description that I am submitting to SCCO for assessment
        And I have no additional costs to declare for the current reporting period
        Then I should see the expected responses on the deputy costs summary page
