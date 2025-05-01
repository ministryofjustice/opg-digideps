@v2 @court-order
Feature: Court order page
    Scenario: A logged out Deputy cannot view the page
        Given I visit the court order page
        Then they get redirected back to the log in page
    
    Scenario: A logged in Deputy cannot view other peoples odrders
        Given I am a deputy (i.e. a deputy logs in)
        When I visit the court order page for someone else
        Then I see a "Page not found" error
    
    Scenario: A logged in deputy views their court order
        Given I am a deputy
        When I visit the court order page
        Then I shoud be on ...
        And see ...
