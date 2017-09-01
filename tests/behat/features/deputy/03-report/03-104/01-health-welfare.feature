Feature: Report 104 health welfare

  @deputy
  Scenario: test HW section
    And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "reports, report-2016, edit-lifestyle, start"
    Then the URL should match "report/\d+/lifestyle/step/1"
    Given the step with the following values CANNOT be submitted:
      | lifestyle[careAppointments] |  |
    And the step with the following values CAN be submitted:
      | lifestyle[careAppointments] | care appointments with physio |
    And I click on "save-and-continue"
    Then the URL should match "report/\d+/lifestyle/step/2"
    # Does Client take part in any leisure or social activities?
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | lifestyle_doesClientUndertakeSocialActivities_1 | no |
    # Tell us about why Ann does not take part in any leisure or social activities
    And the step with the following values CANNOT be submitted:
      | lifestyle_activityDetailsNo |  |
    And the step with the following values CAN be submitted:
      | lifestyle_activityDetailsNo | The client is immobile |
    And I click on "save-and-continue"
    Then the URL should match "report/\d+/lifestyle/step/2"
    # check summary page
    And each text should be present in the corresponding region:
      | care appointments with physio     | care-appointments         |
      | No     | does-client-undertake-socaial-activities |
      | The client is immobile    | activity-details                     |

    # once done, enable submit check in next feature file









