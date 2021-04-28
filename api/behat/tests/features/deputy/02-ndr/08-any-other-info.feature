Feature: NDR any other info

  @ndr
  Scenario: NDR any other info
    Given I am logged in as "behat-lay-deputy-ndr@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "ndr-start, edit-other_info, start"
     # step 1
    And the step cannot be submitted without making a selection
    Then the step with the following values CANNOT be submitted:
      | more_info_actionMoreInfo_0      | yes |       |
      | more_info_actionMoreInfoDetails |     | [ERR] |
    Then the step with the following values CAN be submitted:
      | more_info_actionMoreInfo_0      | yes  |
      | more_info_actionMoreInfoDetails | amid |
    # check summary page
    And each text should be present in the corresponding region:
      | Yes    | more-info         |
      | amid | more-info-details |
    # edit
    When I click on "edit" in the "more-info" region
    Then the step with the following values CAN be submitted:
      | more_info_actionMoreInfo_1      | no  |
    And each text should be present in the corresponding region:
      | No    | more-info         |
    And I should not see the "more-info-details" region
