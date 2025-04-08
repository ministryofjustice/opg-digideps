@v2 @deputyship-details
Feature: List clients for a deputy

    @deputyship-details-client-list
    Scenario: A deputy can see a list of clients whose reports they can contribute to
        Given a lay deputy with surname Tefooliant exists
        When they log in
        And they navigate to the client list page
        Then they should see the no clients message

        Given they have multiple clients
        When they navigate to the client list page
        Then they should see their clients listed in ascending alphabetical order by first name

    @deputyship-details-client-list
    Scenario: A deputy with one client is redirected to the page for that client
        Given a lay deputy with surname Oblobamm exists
        And they have a single client with family name Voltaz
        When they log in
        And they navigate to the client list page
        Then they should be redirected to the page for their single client
