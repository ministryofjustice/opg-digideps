Feature: Report 104 health welfare

  @deputy
  Scenario: load app status taken after 102 non-financial sections are completed
    Given I load the application status from "report-decisions-contacts-visitscare-actions-info"
    And I change the report of the client with case number "12345abc" to "104"

  @deputy
  Scenario: test HW section
    And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "report-start, edit-lifestyle, start"
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








