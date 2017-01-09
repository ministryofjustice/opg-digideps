Feature: NDR actions

  @odr
  Scenario: ODR actions gifts
    Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "odr-start, edit-actions, start"
       # gifts
    And the step cannot be submitted without making a selection
    Then the step with the following values CANNOT be submitted:
      | actions_actionGiveGiftsToClient_0      | yes |       |
      | actions_actionGiveGiftsToClientDetails |     | [ERR] |
    Then the step with the following values CAN be submitted:
      | actions_actionGiveGiftsToClient_0      | yes    |
      | actions_actionGiveGiftsToClientDetails | aggtcd |
    # property
    And the step cannot be submitted without making a selection
    Then the step with the following values CAN be submitted:
      | actions_actionPropertyMaintenance_0 | yes |
    And the step cannot be submitted without making a selection
    Then the step with the following values CAN be submitted:
      | actions_actionPropertySellingRent_0 | yes |
    And the step cannot be submitted without making a selection
    Then the step with the following values CAN be submitted:
      | actions_actionPropertyBuy_0 | yes |
    # check summary page
    And each text should be present in the corresponding region:
      | Yes    | give-gifts-client |
      | aggtcd | give-gifts-client-details |
      | Yes    | property-maintenance |
      | Yes    |  property-selling-rent|
      | Yes    |  property-buy|
    # check step 1 reloaded
    When I click on "edit" in the "give-gifts-client" region
    Then the following fields should have the corresponding values:
      | actions_actionGiveGiftsToClient_0      | yes    |
      | actions_actionGiveGiftsToClientDetails | aggtcd |
    And I go back from the step