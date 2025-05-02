@v2 @court-order
Feature: Court order page

    @lay-combined-high-submitted
    Scenario: A logged out Deputy cannot view the page
        Given I visit the court order page
        Then they get redirected back to the log in page

    @mia @lay-health-welfare-not-started
    Scenario: A logged in deputy views their court order
        Given a Lay Deputy has not started a Health and Welfare report
        And I am associated with one 'hw' court order
        When I visit the page of the court order that 'I am' associated with
        Then I should be on the court order page


#    Scenario: A logged in Deputy cannot view other peoples orders
#        Given I am a deputy
#        When I visit the court order page for someone else
#        Then I see a "Page not found" error

#    Scenario: A multi client deputy can view all of their court orders

#    Scenario: A deputy can no longer view their court order once they've been discharged from the court order
