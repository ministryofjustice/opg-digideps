@pa @paeditreport
Feature: PA user edits report sections

  Scenario: PA user edit decisions section
    Given I load the application status from "pa-users-uploaded"
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I go to "report/7/decisions"
    Then the response status code should be 200

  Scenario: PA user cannot access decisions section in a report they don't own
    Given I am logged in as "behat-pa2@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I go to "report/7/decisions"
    #Shouldn't this be 401 unauthorized?
    Then the response status code should be 500