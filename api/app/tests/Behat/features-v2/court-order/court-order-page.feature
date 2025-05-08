@v2 @court-order
Feature: Court order page

    @lay-combined-high-submitted
    Scenario: A logged out Deputy cannot view the page
        Given I visit the court order page
        Then they get redirected back to the log in page

    @lay-health-welfare-not-started
    Scenario: A logged in deputy views their court order
        Given a Lay Deputy has not started a Health and Welfare report
        And I am associated with '1' 'hw' court order(s)
        When I visit the page of a court order that 'I am' associated with
        Then I should be on the court order page

    @lay-pfa-low-not-started
    Scenario: A logged in deputy cannot view a court order that's not assigned to them
        Given a Lay Deputy has not started a Pfa Low Assets report
        And I am associated with '1' 'pfa' court order(s)
        When I visit the page of a court order that 'I am not' associated with
        Then I should be redirected and denied access to view court order

#    @lay-pfa-high-not-started-multi-client-deputy
#    Scenario: A multi client deputy can view all of their court orders
#        And a Lay Deputy tries to login with their "primary" email address
#        And I am associated with '2' 'pfa' court order(s)
#        When I visit the page of a court order that 'I am' associated with
#        Then I should be redirected and denied access to view court order


#    Scenario: A deputy can no longer view their court order once they've been discharged from the court order
