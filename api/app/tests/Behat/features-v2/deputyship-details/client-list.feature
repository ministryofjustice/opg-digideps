@v2 @deputyship-details
Feature: List clients for a deputy

    @deputyship-details-client-list
    Scenario: A deputy can see a list of clients whose reports they can contribute to
        Given a lay deputy with multiple clients exists
        When that lay deputy logs in
        And they navigate to the client list page
        Then they should see their clients listed in ascending alphabetical order by first name
