@v2
Feature: Providing feedback after submitting a report

  Scenario: A user provides satisfaction feedback
    Given a Lay Deputy completes and submits a report
    When I provide some post-submission feedback
    Then I should be on the user research feedback page
    When I click on "Your reports"
    Then I should be on the reports overview page

  Scenario: A user provides user research feedback
    Given a Lay Deputy provides post-submission feedback
    When I provide valid user research responses
    Then I should be on the user research feedback submitted page
    When I click on "Your reports"
    Then I should be on the reports overview page
