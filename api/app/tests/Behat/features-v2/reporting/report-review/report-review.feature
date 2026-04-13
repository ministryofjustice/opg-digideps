@v2 @v2_reporting_1 @report-review
Feature: Report review

  @lay-pfa-high-submitted
  Scenario: A Lay Deputy can see the report review page
    Given a Lay Deputy has submitted a report

    # note: the report we're adding an expense to is not the current report,
    # as that report has been submitted; the current report is still waiting to be completed
    And that report includes deputy expenses
    When I view the report review page
    Then I should see the submitting user's details in the deputy section
    And I should see the correct details in the deputy expenses section
