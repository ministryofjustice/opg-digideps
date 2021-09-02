@v2 @v2_reporting @section-navigation
Feature: Section navigation - Professional (see section-navigation.lay.combined.feature for other shared sections)

    @prof-named-pfa-high-not-started
    Scenario: Deputy Costs
        Given a Professional Deputy has not started a Pfa High Assets report
        When I visit the deputy costs report section
        Then the previous section should be "Debts"
        And the next section should be "Deputy costs estimate"

    @prof-named-pfa-high-not-started
    Scenario: Deputy Cost Estimates
        Given a Professional Deputy has not started a Pfa High Assets report
        When I visit the deputy costs estimate report section
        Then the previous section should be "Deputy costs"
        And the next section should be "Actions"

    @prof-named-pfa-high-not-started
    Scenario: Debts
        Given a Professional Deputy has not started a Pfa High Assets report
        When I visit the debts report section
        Then the previous section should be "Assets"
        And the next section should be "Deputy costs"

    @prof-named-pfa-high-not-started
    Scenario: Actions
        Given a Professional Deputy has not started a Pfa High Assets report
        When I visit the actions report section
        Then the previous section should be "Deputy costs estimate"
        And the next section should be "Any other information"
