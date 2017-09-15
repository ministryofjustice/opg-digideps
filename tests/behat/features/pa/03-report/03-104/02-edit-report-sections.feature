Feature: PA user edits 104 report sections

  @deputy @104
  Scenario: Complete lifestyle section

    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I change the report of the client with case number "1000014" to "104-6"
    # assert not submittable yet
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    # click on 104 report
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-lifestyle, start"
    Then the URL should match "report/\d+/lifestyle/step/1"
    Given the step with the following values CANNOT be submitted:
      | lifestyle_careAppointments |  |
    And the step with the following values CAN be submitted:
      | lifestyle_careAppointments | care appointments with physio |
    And I click on "save-and-continue"
    Then the URL should match "report/\d+/lifestyle/step/2"
    # Does Client take part in any leisure or social activities?
    Given the step cannot be submitted without making a selection
    And the step with the following values CANNOT be submitted:
      | lifestyle_doesClientUndertakeSocialActivities_1 | no |
    # Tell us about why Client does not take part in any leisure or social activities
    And the step with the following values CANNOT be submitted:
      | lifestyle_doesClientUndertakeSocialActivities_1 | no |
      | lifestyle_activityDetailsNo |  |
    And the step with the following values CAN be submitted:
      | lifestyle_doesClientUndertakeSocialActivities_1 | no |
      | lifestyle_activityDetailsNo | The client is immobile |
    # check summary page
    Then the URL should match "report/\d+/lifestyle/summary"
    And each text should be present in the corresponding region:
      | care appointments with physio     | care-appointments         |
      | No     | does-client-undertake-social-activities |
      | The client is immobile    | activity-details                     |
    And I save the application status into "104-report-completed"
