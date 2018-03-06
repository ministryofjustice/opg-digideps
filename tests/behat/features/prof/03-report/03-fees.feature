Feature: PROF fees

  Scenario: fees
    Given I am logged in as "behat-prof1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000010" region
    And I click on "edit-prof_current_fees, start"
    # chose "no option"
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | prof_service_fees_currentProfPaymentsReceived_1 | no |
    And each text should be present in the corresponding region:
      | no | has-fees |

