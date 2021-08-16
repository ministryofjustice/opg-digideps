@v2 @js-basic-check @js
Feature: Javascript basic checks

  @lay-pfa-high-not-started
  Scenario: A user checks some basic java script functionality
    Given a Lay Deputy has not started a report
    And I view and start the visits and care report section
    Then I confirm I do not live with the client
