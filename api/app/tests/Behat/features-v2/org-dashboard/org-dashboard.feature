@v2 @org-dashboard
Feature: Organisation dashboard

    @THIS-ONE
    Scenario: A PA admin views the list of reports without filtering
        Given a PA admin with email "bob.scratchit@great.expectations" logs in to the frontend
        And there are 2 reports which are "not started"
        Then I should see 2 reports on the org dashboard page
