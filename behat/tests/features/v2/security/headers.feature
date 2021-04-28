Feature: headers

    @security
    Scenario: ensure Strict-Transport-Security header exists
        Given I send a GET request to "/login"
        Then the response status code should be 200
        And the header "Strict-Transport-Security" should be equal to "max-age=31536000; includeSubdomains;"

    @security
    Scenario: ensure 'Server' and 'X-Powered-By' headers do not exist
        Given I send a GET request to "/login"
        Then the response status code should be 200
        And the header "Server" should not exist
        And the header "X-Powered-By" should not exist
