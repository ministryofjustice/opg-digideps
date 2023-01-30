@report-submissions @v2_sequential_1 @v2
Feature: Report submissions dashboard

    @super-admin
    Scenario: Searching for submissions - client name
        Given two submitted reports with clients sharing the same 'first' name exist
        Given two submitted reports with clients sharing the same 'last' name exist
        And a super admin user accesses the admin app
        When I navigate to the admin report submissions page
        And I search for submissions using the 'first' name of the clients with the same 'first' name
        Then I should see the clients with the same 'first' names in the search results
        And I should not see the two clients with different 'last' names
        When I search for submissions using the 'last' name of the clients with the same 'last' name
        Then I should see the clients with the same 'last' names in the search results
        And I should not see the two clients with different 'first' names

    @super-admin
    Scenario: Searching for submissions - case number
        Given a client has submitted two reports
        And another client has submitted one report
        And a super admin user accesses the admin app
        When I navigate to the admin report submissions page
        And I search for submissions using the court order number of the client with 'two' reports
        Then I should see 'two' rows for the client with 'two' report submissions in the search results
        And I should not see the client with 'one' report submission in the search results
        When I search for submissions using the court order number of the client with 'one' report
        Then I should see 'one' rows for the client with 'one' report submission in the search results
        And I should not see the client with 'two' report submissions in the search results

    @super-admin
    Scenario: Manual submission archive
        Given a client has submitted one report
        And a super admin user accesses the admin app
        When I navigate to the admin report submissions page
        And I search for submissions using the court order number of the client with 'one' report
        And I manually 'archive' the client that has one submitted report
        Then I should see the client row under the Synchronised tab

    @super-admin
    Scenario: Manually trigger synchronisation
        Given a client has submitted one report
        And there was an error during synchronisation
        And a super admin user accesses the admin app
        When I navigate to the admin report submissions page
        And I search for submissions using the court order number of the client with 'one' report
        Then the status of the documents for the client with one report submission should be 'Permanent Fail'
        And I manually 'synchronise' the client that has one submitted report
        Then the status of the documents for the client with one report submission should be 'Queued'


    @super-admin
    Scenario: Make 'New' tab visibility toggle based on Document Sync Enabled flag
        And a super admin user accesses the admin app
        Given the document sync enabled flag is set to '0'
        And I navigate to the admin report submissions page
        Then the 'New' tab 'is' visible
        Then the 'Pending' tab 'is' visible
        Then the 'Synchronised' tab 'is' visible
        Given the document sync enabled flag is set to '1'
        And I navigate to the admin report submissions page
        Then the 'New' tab 'is not' visible
        Then the 'Pending' tab 'is' visible
        Then the 'Synchronised' tab 'is' visible
