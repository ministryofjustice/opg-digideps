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

    @prof-admin-not-started
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

    @prof-admin-not-started
    Scenario: A professional deputy has both fixed and assessed deputy costs - in line with Practice Direction 19B
        Given a Professional Admin Deputy has not started a report
        When I visit and start the deputy costs report section for an existing client
        And I have fixed and assessed deputy costs to declare
        And my client has not paid me in the current reporting period for work from a previous period
        And I have charged in line with interim billing under Practice Direction 19B
        And I have provided valid interim costs and dates for all three periods
        And I enter a valid amount and description that I am submitting to SCCO for assessment
        And I have no additional costs to declare for the current reporting period
        Then I should see the expected responses on the deputy costs summary page

    @prof-admin-not-started
    Scenario: A professional deputy has both fixed and assessed deputy costs - not in line with Practice Direction 19B
        Given a Professional Admin Deputy has not started a report
        When I visit and start the deputy costs report section for an existing client
        And I have fixed and assessed deputy costs to declare
        And my client has not paid me in the current reporting period for work from a previous period
        And I have not charged in line with interim billing under Practice Direction 19B
        And I enter a valid amount for the current reporting period costs
        And I enter a valid amount and description that I am submitting to SCCO for assessment
        And I have no additional costs to declare for the current reporting period
        Then I should see the expected responses on the deputy costs summary page

    @prof-admin-not-started
    Scenario: A professional deputy has charged for previous periods in this period
        Given a Professional Admin Deputy has not started a report
        When I visit and start the deputy costs report section for an existing client
        And I have fixed deputy costs to declare
        And my client has paid me in the current reporting period for work from a previous period
        And I declare two previous costs with valid dates and amounts
        And I enter a valid amount for the current reporting period costs
        And I have no additional costs to declare for the current reporting period
        Then I should see the expected responses on the deputy costs summary page

    @prof-admin-not-started
    Scenario: A professional deputy has additional costs to declare
        Given a Professional Admin Deputy has not started a report
        When I visit and start the deputy costs report section for an existing client
        And I have fixed deputy costs to declare
        And my client has paid me in the current reporting period for work from a previous period
        And I declare two previous costs with valid dates and amounts
        And I enter a valid amount for the current reporting period costs
        And I have additional costs in all seven categories to declare for the current reporting period
        Then I should see the expected responses on the deputy costs summary page

    @prof-admin-not-started
    Scenario: A professional deputy edits all available questions from the summary page - fixed costs
        Given a Professional Admin Deputy has not started a report
        When I visit and start the deputy costs report section for an existing client
        And I provide all required information for fixed costs with previous period and additional costs
        And I edit the details of a cost incurred in a previous period
        And I edit the amount of costs incurred in the current period
        And I edit the amount of an additional cost incurred in the current period
        Then I should see the expected responses on the deputy costs summary page
        When I change the type of costs incurred to 'Assessed' costs
        Then there should be '2' new questions to answer

    @prof-admin-not-started @acs2
    Scenario: A professional deputy edits all available questions from the summary page - assessed costs
        Given a Professional Admin Deputy has not started a report
        When I visit and start the deputy costs report section for an existing client
        And I provide all required information for assessed costs without previous period and additional costs
        And I edit the amount of one of the interim interim billing under Practice Direction 19B
        And I edit the amount being submitted to SCCO for assessment
        Then I should see the expected responses on the deputy costs summary page
        When I change my response to charged in line with interim billing under Practice Direction 19B to no
        Then there should be '1' new questions to answer
        When I change the type of costs incurred to 'Fixed' costs
        Then there should be '1' new questions to answer
