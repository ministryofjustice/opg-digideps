Feature: headers

    @security
    Scenario: ensure Strict-Transport-Security header exists
        Given I send a GET request to "/login"
        Then the response status code should be 200
        And the header "Strict-Transport-Security" should be equal to "max-age=31536000; includeSubdomains;"
