@v2 @section-navigation
Feature: Section navigation - Lay Health and Welfare

    @lay-combined-high-not-started
    Scenario: Health and Lifestyle
        Given a Lay Deputy has not started a Health and Welfare report
        When I visit the accounts report section
        Then the previous section should be "xxx"
        And the next section should be "xxx"
