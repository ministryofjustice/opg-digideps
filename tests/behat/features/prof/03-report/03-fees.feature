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
    # change to yes
    When I click on "edit-exists" in the "has-fees"
    And the step with the following values CAN be submitted:
      | prof_service_fees_currentProfPaymentsReceived_0 | yes |
    Given the step cannot be submitted without making a selection
    Then the step with the following values CAN be submitted:
      | prof_service_fee_type_serviceTypeId_0 | annual-report |
    # Add a charge: empty
    When I press "prof_service_fee_type_saveAndAddAnother"
    Then the following fields should have an error:
      | prof_service_fee_type_assessedOrFixed_0 |
      | prof_service_fee_type_assessedOrFixed_1 |
      | prof_service_fee_type_amountCharged     |
      | prof_service_fee_type_paymentReceived_0 |
      | prof_service_fee_type_paymentReceived_1 |
    # Add a charge: empty paymeny received (yes to last answer)
    When I fill in the following:
      | prof_service_fee_type_assessedOrFixed_0 | fixed |
      | prof_service_fee_type_amountCharged     | 1234  |
      | prof_service_fee_type_paymentReceived_0 | yes   |
    When I press "prof_service_fee_type_save"
    Then the following fields should have an error:
      | prof_service_fee_type_amountReceived            |
      | prof_service_fee_type_paymentReceivedDate_day   |
      | prof_service_fee_type_paymentReceivedDate_month |
      | prof_service_fee_type_paymentReceivedDate_year  |
    # Add a charge: complete
    When I fill in the following:
      | prof_service_fee_type_assessedOrFixed_0         | fixed |
      | prof_service_fee_type_amountCharged             | 1234  |
      | prof_service_fee_type_paymentReceived_0         | yes   |
      | prof_service_fee_type_amountReceived            | 9876  |
      | prof_service_fee_type_paymentReceivedDate_day   | 1     |
      | prof_service_fee_type_paymentReceivedDate_month | 1     |
      | prof_service_fee_type_paymentReceivedDate_year  | 2018  |
    When I press "prof_service_fee_type_save"
    Then the form should be valid
    # summary page
    


