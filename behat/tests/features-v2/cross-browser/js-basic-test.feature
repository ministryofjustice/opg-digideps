@v2 @js-basic-check
Feature: Javascript basic check

  Scenario: A user checks some basic java script functionality
    Given a Lay Deputy has not started a report
    When I view and start visits and care section
    And I enter that I do not live with client
    And I fill in "visits_care[howOftenDoYouContactClient]" with "blah blah"
