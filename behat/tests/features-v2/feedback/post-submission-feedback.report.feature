@v2
Feature: Providing feedback after submitting a report
  Scenario: A user does not provide feedback
    Given a Lay Deputy completes and submits a report
    Then I should be on the report submitted page
    When I press "Your reports"
    Then I should be on the Lay main overview page

  Scenario: A user provides satisfaction feedback
    Given a Lay Deputy completes and submits a report
    Then I should be on the report submitted page
    When I provide some post-submission feedback
    Then I should be on the post-submission user research page
  @acs
  Scenario: A user provides user research feedback
    Given a Lay Deputy has submitted a report
    When I provide valid user research responses
    Then I should be on the user research feedback submitted page
    When I press "Your reports"
    Then I should be on the Lay main overview page
