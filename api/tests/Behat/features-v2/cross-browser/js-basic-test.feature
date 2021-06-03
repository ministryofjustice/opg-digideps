@v2 @js-basic-check @js
Feature: Javascript basic checks

  @lay-pfa-high-not-started
  Scenario: A user checks some basic java script functionality
    Given a Lay Deputy has not started a report
    When I view and start visits and care section
    And I enter that I do not live with client
    Then I can fill in a text box with how often I visit client
