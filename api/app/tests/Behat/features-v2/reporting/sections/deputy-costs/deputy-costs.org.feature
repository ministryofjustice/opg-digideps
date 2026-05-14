@v2 @v2_reporting_2 @deputy-costs
Feature: Deputy costs - Applies to Org users only

    @prof-admin-health-welfare-not-started
    Scenario: A professional deputy has fixed deputy costs only
        Given a Professional Admin Deputy has not started a report
        When I navigate to and start the deputy costs report section for an existing client
        And I have fixed deputy costs to declare
        And my client has not paid me in the current reporting period for work from a previous period
        And I enter a valid amount for the current reporting period costs
        And I have no additional costs to declare for the current reporting period
        Then I should see the expected responses on the deputy costs summary page
        When I follow link back to report overview page
        Then I should see "prof-deputy-costs" as "finished"

    @prof-admin-health-welfare-not-started
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

    @prof-admin-health-welfare-not-started
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
        When I follow link back to report overview page
        Then I should see "prof-deputy-costs" as "finished"

    @prof-admin-health-welfare-not-started
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

    @prof-admin-health-welfare-not-started
    Scenario: A professional deputy has charged for previous periods in this period
        Given a Professional Admin Deputy has not started a report
        When I visit and start the deputy costs report section for an existing client
        And I have fixed deputy costs to declare
        And my client has paid me in the current reporting period for work from a previous period
        And I declare 2 previous costs with valid dates and amounts
        And I enter a valid amount for the current reporting period costs
        And I have no additional costs to declare for the current reporting period
        Then I should see the expected responses on the deputy costs summary page

    @prof-admin-health-welfare-not-started
    Scenario: A professional deputy has additional costs to declare
        Given a Professional Admin Deputy has not started a report
        When I visit and start the deputy costs report section for an existing client
        And I have fixed deputy costs to declare
        And my client has paid me in the current reporting period for work from a previous period
        And I declare 2 previous costs with valid dates and amounts
        And I enter a valid amount for the current reporting period costs
        And I have additional costs in all seven categories to declare for the current reporting period
        Then I should see the expected responses on the deputy costs summary page

    @prof-admin-health-welfare-not-started
    Scenario: A professional deputy edits all available questions from the summary page - fixed costs
        Given a Professional Admin Deputy has not started a report
        When I visit and start the deputy costs report section for an existing client
        And I provide all required information for fixed costs with previous period and additional costs
        And I edit the details of a cost incurred in a previous period
        And I edit the amount of costs incurred in the current period
        And I edit the amount of an additional cost incurred in the current period
        Then I should see the expected responses on the deputy costs summary page
        When I change the type of costs incurred to 'Assessed' costs
        Then there should be 2 new questions to answer
        When I follow link back to report overview page
        Then I should see "prof-deputy-costs" as "not finished"

    @prof-admin-health-welfare-not-started
    Scenario: A professional deputy edits all available questions from the summary page - assessed costs
        Given a Professional Admin Deputy has not started a report
        When I visit and start the deputy costs report section for an existing client
        And I provide all required information for assessed costs without previous period and additional costs
        And I edit the amount of one of the interim interim billing under Practice Direction 19B
        And I edit the amount being submitted to SCCO for assessment
        Then I should see the expected responses on the deputy costs summary page
        When I change my response to charged in line with interim billing under Practice Direction 19B to no
        Then there should be 1 new question to answer
        When I change the type of costs incurred to 'Fixed' costs
        Then there should be 1 new question to answer

    @prof-admin-health-welfare-not-started
    Scenario: A professional deputy adds a previous period cost from the summary page
        Given a Professional Admin Deputy has not started a report
        When I visit and start the deputy costs report section for an existing client
        And I provide all required information for fixed costs with previous period and additional costs
        And I add an additional cost for a previous period from the summary page
        Then I should see the expected responses on the deputy costs summary page

    @prof-admin-health-welfare-not-started
    Scenario: A professional deputy removes a previous period cost from the summary page
        Given a Professional Admin Deputy has not started a report
        When I visit and start the deputy costs report section for an existing client
        And I provide all required information for fixed costs with previous period and additional costs
        And I remove an additional cost for a previous period from the summary page
        Then I should see the expected responses on the deputy costs summary page

    @prof-admin-health-welfare-not-started
    Scenario: A professional deputy provides invalid information - fixed costs
        Given a Professional Admin Deputy has not started a report
        When I visit and start the deputy costs report section for an existing client
        When I don't provide details of the costs I've incurred
        Then I should see a 'missing cost type' deputy costs error
        When I have fixed deputy costs to declare
        And my client has not paid me in the current reporting period for work from a previous period
        And I don't provide a value for current reporting period fixed costs
        Then I should see a 'missing fixed cost amount' deputy costs error
        When I enter a valid amount for the current reporting period costs
        Then I should be on the deputy costs - breakdown page

    @prof-admin-health-welfare-not-started
    Scenario: A professional deputy provides invalid information - previous reporting costs
        Given a Professional Admin Deputy has not started a report
        When I visit and start the deputy costs report section for an existing client
        And I have fixed deputy costs to declare
        And I don't choose a response for previous costs
        Then I should see a 'Please choose yes or no' deputy costs error
        When my client has paid me in the current reporting period for work from a previous period
        And I don't provide any details on the previous costs
        Then I should see an 'empty dates and value' deputy costs error
        When I provide an end date that is before the start date
        Then I should see an 'end date before start date' deputy costs error
        When I provide a negative amount value
        Then I should see an 'amount limit' deputy costs error
        When I declare 1 previous cost with valid dates and amounts
        Then I should be on the deputy costs - costs received page

    @prof-admin-health-welfare-not-started
    Scenario: A professional deputy provides invalid information - assessed costs
        Given a Professional Admin Deputy has not started a report
        When I visit and start the deputy costs report section for an existing client
        And I have assessed deputy costs to declare
        And my client has not paid me in the current reporting period for work from a previous period
        And I have charged in line with interim billing under Practice Direction 19B
        When I don't provide any interim cost details
        Then I should see a 'at least one cost required' deputy costs error
        When I provide a valid interim cost amount with a missing date
        Then I should see a 'date required' deputy costs error
        When I provide a valid interim cost date and a missing amount
        Then I should see a 'amount required' deputy costs error
        When I provide a valid interim cost date and an amount outside the amount limit
        Then I should see an 'amount outside of limit' deputy costs error
        When I have provided valid interim costs and dates for all three periods
        Then I should be on the deputy costs - SCCO amount page

    @prof-admin-health-welfare-not-started
    Scenario: A professional deputy provides invalid information - SCCO assessed costs
        Given a Professional Admin Deputy has not started a report
        When I visit and start the deputy costs report section for an existing client
        And I have assessed deputy costs to declare
        And my client has not paid me in the current reporting period for work from a previous period
        And I have charged in line with interim billing under Practice Direction 19B
        And I have provided valid interim costs and dates for all three periods
        And I don't enter an SCCO assessed cost amount
        Then I should see a 'missing SCCO assesssed cost amount' deputy costs error
        When I enter a negative SCCO assessed cost amount
        Then I should see a 'negative SCCO assessed cost amount' deputy costs error
        And I enter a valid amount and description that I am submitting to SCCO for assessment
        Then I should be on the deputy costs - breakdown page

    @prof-admin-health-welfare-not-started
    Scenario: A professional deputy provides invalid information - additional costs
        Given a Professional Admin Deputy has not started a report
        When I visit and start the deputy costs report section for an existing client
        And I have fixed deputy costs to declare
        And my client has paid me in the current reporting period for work from a previous period
        And I declare 1 previous costs with valid dates and amounts
        And I enter a valid amount for the current reporting period costs
        And I provide 6 negative and 1 too large amounts for all seven additional cost types
        Then I should see '6 negative and 1 too large amounts' deputy costs errors
        And I provide a valid 'Other' cost but no description
        Then I should see a 'missing other cost description' deputy costs error
        And I have additional costs in all seven categories to declare for the current reporting period
