Feature: gifts

  @deputy
  Scenario: gifts
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "reports, report-2016, edit-gifts, start"
    # chose "no records"
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | gift_exist_giftsExist_1 | no |
        # summary page check
    And each text should be present in the corresponding region:
      | No | gifts-exist |
        # select there are records (from summary page link)
    Given I click on "edit" in the "gifts-exist" region
    And the step with the following values CAN be submitted:
      | gift_exist_giftsExist_0 | yes |
        # add expense n.1 (and validate form)
    And the step with the following values CANNOT be submitted:
      | gifts_single_explanation |  | [ERR] |
      | gifts_single_amount      |  | [ERR] |
    And the step with the following values CANNOT be submitted:
      | gifts_single_explanation |                | [ERR] |
      | gifts_single_amount      | invalid number | [ERR] |
    And the step with the following values CANNOT be submitted:
      | gifts_single_explanation |     | [ERR] |
      | gifts_single_amount      | 0.0 | [ERR] |
    And the step with the following values CAN be submitted:
      | gifts_single_explanation | birthday gift to daughter |
      | gifts_single_amount      | 35                        |
        # add expense n.2
    And I choose "yes" when asked for adding another record
    And the step with the following values CAN be submitted:
      | gifts_single_explanation | gift for the dog |
      | gifts_single_amount      | 14               |
        # add another: no
    And I choose "no" when asked for adding another record
        # check record in summary page
    And each text should be present in the corresponding region:
      | birthday gift to daughter | gift-birthday-gift-to-daughter |
      | £35.00                    | gift-birthday-gift-to-daughter |
      | gift for the dog          | gift-gift-for-the-dog          |
      | £14.00                    | gift-gift-for-the-dog          |
      | £49.00                    | gift-total                     |
        # remove expense n.2
    When I click on "delete" in the "gift-gift-for-the-dog" region
    Then I should not see the "gift-gift-for-the-dog" region
        # test add link
    When I click on "add"
    Then I should see the "save-and-continue" link
    When I go back from the step
        # edit expense n.1
    When I click on "edit" in the "gift-birthday-gift-to-daughter" region
    Then the following fields should have the corresponding values:
      | gifts_single_explanation | birthday gift to daughter |
      | gifts_single_amount      | 35.00                     |
    And the step with the following values CAN be submitted:
      | gifts_single_explanation | birthday gift to the daughter |
      | gifts_single_amount      | 45                            |
    And each text should be present in the corresponding region:
      | birthday gift to the daughter | gift-birthday-gift-to-the-daughter |
      | £45.00                        | gift-birthday-gift-to-the-daughter |
