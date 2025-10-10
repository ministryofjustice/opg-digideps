@v2 @org-dashboard
Feature: Organisation dashboard

    @org-dashboard-no-filter
    Scenario: A PA admin views the list of reports without filtering
        Given the organisation "Follicle Beatitude" with email identifier "great.expectations" exists
        And a PA admin user with email "bob.scratchit@great.expectations" exists
        And "bob.scratchit@great.expectations" is in the "Follicle Beatitude" organisation
        And "bob.scratchit@great.expectations" logs in
        And there is 1 report which is "notStarted" associated with "Follicle Beatitude"
        And there is 1 report which is "notFinished" associated with "Follicle Beatitude"
        When they visit the org dashboard page
        Then there should be 2 reports on the org dashboard page

    @org-dashboard-not-started-filter
    Scenario: A PA admin views the list of reports, filtered by status "not started"
        Given the organisation "Smurm Alloys" with email identifier "smurm.berg" exists
        And a PA admin user with email "smoman@smurm.berg" exists
        And "smoman@smurm.berg" is in the "Smurm Alloys" organisation
        And "smoman@smurm.berg" logs in
        And there are 2 reports which are "notStarted" associated with "Smurm Alloys"
        And there is 1 report which is "readyToSubmit" associated with "Smurm Alloys"
        When I visit the org dashboard page
        And I select "notStarted" from "status"
        And I press the search button
        Then there should be 2 reports on the org dashboard page

    @org-dashboard-ready-to-submit-filter
    Scenario: A PA admin views the list of reports, filtered by status "ready to submit"
        Given the organisation "Vob Fasteners" with email identifier "vob.fasten" exists
        And a PA admin user with email "lurch.foo@vob.fasten" exists
        And "lurch.foo@vob.fasten" is in the "Vob Fasteners" organisation
        And "lurch.foo@vob.fasten" logs in
        And there are 2 reports which are "notStarted" associated with "Vob Fasteners"
        And there are 2 reports which are "readyToSubmit" associated with "Vob Fasteners"
        When I visit the org dashboard page
        And I select "readyToSubmit" from "status"
        And I press the search button
        Then there should be 2 reports on the org dashboard page

    @org-dashboard-in-progress-filter
    Scenario: A PA admin views the list of reports, filtered by status "in progress"
        Given the organisation "Zoot Suits" with email identifier "zoot.suits" exists
        And a PA admin user with email "helen.sjamn@zoot.suits" exists
        And "helen.sjamn@zoot.suits" is in the "Zoot Suits" organisation
        And "helen.sjamn@zoot.suits" logs in
        And there are 2 reports which are "notFinished" associated with "Zoot Suits"
        And there are 2 reports which are "readyToSubmit" associated with "Zoot Suits"
        When I visit the org dashboard page
        And I select "notFinished" from "status"
        And I press the search button
        Then there should be 2 reports on the org dashboard page
