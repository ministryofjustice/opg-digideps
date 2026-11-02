@v2 @v2_reporting_1 @section-navigation
Feature: Section navigation - Public Authority (see section-navigation.lay.combined.feature for other shared sections)

    @pa-admin-combined-high-not-started
    Scenario: Deputy fees and expenses
        Given a Public Authority Deputy has not started a Combined High Assets report
        When I visit the deputy fees and expenses section
        Then the previous section should be "Debts"
        And the next section should be "Actions you plan to take"

    @pa-admin-combined-high-not-started
    Scenario: Gifts
        Given a Public Authority Deputy has not started a Combined High Assets report
        When I visit the gifts report section
        Then the previous section should be "Accounts"
        And the next section should be "Money transfers"

    @pa-admin-combined-high-not-started
    Scenario: Health and lifestyle
        Given a Public Authority Deputy has not started a Combined High Assets report
        When I visit the health and lifestyle report section
        Then the previous section should be "Visits and care"
        And the next section should be "Benefits check and money others received"
