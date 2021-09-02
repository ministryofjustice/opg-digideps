@v2 @v2_reporting @section-navigation
Feature: Section navigation - Public Authority (see section-navigation.lay.combined.feature for other shared sections)

    @pa-admin-combined-high-not-started
    Scenario: Deputy fees and expenses
        Given a Public Authority Deputy has not started a Combined High Assets report
        When I visit the deputy fees and expenses section
        Then the previous section should be "Health and lifestyle"
        And the next section should be "Gifts"

    @pa-admin-combined-high-not-started
    Scenario: Gifts
        Given a Public Authority Deputy has not started a Combined High Assets report
        When I visit the gifts report section
        Then the previous section should be "Deputy fees and expenses"
        And the next section should be "Actions"

    @pa-admin-combined-high-not-started
    Scenario: Health and lifestyle
        Given a Public Authority Deputy has not started a Combined High Assets report
        When I visit the health and lifestyle report section
        Then the previous section should be "Visits and care"
        And the next section should be "Deputy fees and expenses"
