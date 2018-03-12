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
      | No | has-fees |
    # change to yes
    When I click on "edit-exist" in the "has-fees" region
    And the step with the following values CAN be submitted:
      | prof_service_fees_currentProfPaymentsReceived_0 | yes |
    Given the step cannot be submitted without making a selection
    Then the step with the following values CAN be submitted:
      | prof_service_fee_type_serviceTypeId_0 | annual-report |
    # Add a charge: empty
    When I press "prof_service_fee_type_saveAndAddAnother"
    Then the following fields should have an error:
      | prof_service_fee_type_assessedOrFixed_0         |
      | prof_service_fee_type_assessedOrFixed_1         |
      | prof_service_fee_type_amountCharged             |
      | prof_service_fee_type_paymentReceived_0         |
      | prof_service_fee_type_paymentReceived_1         |
      | prof_service_fee_type_amountReceived            |
      | prof_service_fee_type_paymentReceivedDate_day   |
      | prof_service_fee_type_paymentReceivedDate_month |
      | prof_service_fee_type_paymentReceivedDate_year  |
    # Add a charge: empty date (yes to last answer)
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
    # Add a fixed charge with a payment:
    When I fill in the following:
      | prof_service_fee_type_assessedOrFixed_0         | fixed |
      | prof_service_fee_type_amountCharged             | 1234  |
      | prof_service_fee_type_paymentReceived_0         | yes   |
      | prof_service_fee_type_amountReceived            | 9876  |
      | prof_service_fee_type_paymentReceivedDate_day   | 1     |
      | prof_service_fee_type_paymentReceivedDate_month | 1     |
      | prof_service_fee_type_paymentReceivedDate_year  | 2018  |
    When I click on "save-and-add-another"
    Then the form should be valid
    # Add another item: assessed-cost charge without payment:
    And the step with the following values CAN be submitted:
      | prof_service_fee_type_serviceTypeId_3 | appointment |
    And I fill in the following:
      | prof_service_fee_type_assessedOrFixed_1 | assessed |
      | prof_service_fee_type_amountCharged     | 456      |
      | prof_service_fee_type_paymentReceived_1 | no       |
    When I click on "save-and-continue"
    Then the form should be valid
    # estimate of your costs
    Then the step cannot be submitted without making a selection
    But the step with the following values CAN be submitted:
      | prof_service_fees_previousProfFeesEstimateGiven_0 | yes              |
      | prof_service_fees_profFeesEstimateSccoReason      | scoo-reason-test |
    #summary page
    Then each text should be present in the corresponding region:
      | 1,234.00         | service-fee-annual-report |
      | 1 January 2018   | service-fee-annual-report |
      | Yes              | previous-estimates        |
      | scoo-reason-test | scco-reason               |
      | 1,690            | grand-total-charged       |
      | 9,876            | grand-total-received      |


  Scenario: fees edit + remove
    Given I save the application status into "prof-fees-expenses-before-edit"
    And I am logged in as "behat-prof1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000010" region
    And I click on "edit-prof_current_fees"
    When I click on "edit-fee" in the "service-fee-annual-report" region
    Then the following fields should have the corresponding values:
      | prof_service_fee_type_serviceTypeId_0 | annual-report |
    And the step with the following values CAN be submitted:
      | prof_service_fee_type_serviceTypeId_1 | annual-management-interim |
    And the following fields should have the corresponding values:
      | prof_service_fee_type_assessedOrFixed_0         | fixed    |
      | prof_service_fee_type_amountCharged             | 1,234.00 |
      | prof_service_fee_type_paymentReceived_0         | yes      |
      | prof_service_fee_type_amountReceived            | 9,876.00 |
      | prof_service_fee_type_paymentReceivedDate_day   | 01       |
      | prof_service_fee_type_paymentReceivedDate_month | 01       |
      | prof_service_fee_type_paymentReceivedDate_year  | 2018     |
    And the step with the following values CAN be submitted:
      | prof_service_fee_type_assessedOrFixed_1 | assessed |
      | prof_service_fee_type_amountCharged     | 11223344 |
      | prof_service_fee_type_paymentReceived_0 | no       |
    # summary page again fix
    Then each text should be present in the corresponding region:
      | Yes              | has-fees                               |
      | 11,223,344       | assessed-fee-annual-management-interim |
      | Yes              | previous-estimates                     |
      | scoo-reason-test | scco-reason                            |
    # remove appointment
    Given I load the application status from "prof-fees-expenses-before-edit"
    When I click on "delete" in the "assessed-fee-annual-management-interim" region
    Then I should not see the "assessed-fee-annual-management-interim" region
    # change initial question to "no", check you are in start page
     # change to yes
    When I click on "edit-exist" in the "has-fees" region
    And the step with the following values CAN be submitted:
      | prof_service_fees_currentProfPaymentsReceived_1 | no |
    Then I should see the "start-page" region
    # restore data before scenario
    And I load the application status from "prof-fees-expenses-before-edit"



