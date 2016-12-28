Feature: odr / actions / gifts

  @odr
  Scenario: ODR actions gifts
    Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
#    And I click on "odr-start, edit-actions, action-gifts"
#    And I save the page as "odr-actions-gifts-empty"
#     # empty form
#    When I press "odr_action_gift_save"
#    Then the following fields should have an error:
#      | odr_action_gift_actionGiveGiftsToClient_0 |
#      | odr_action_gift_actionGiveGiftsToClient_1 |
#    # no
#    When I fill in the following:
#      |  odr_action_gift_actionGiveGiftsToClient_1 | no |
#    And I press "odr_action_gift_save"
#    Then the form should be valid
#    When I click on "action-gifts"
#    Then the following fields should have the corresponding values:
#      |  odr_action_gift_actionGiveGiftsToClient_1 | no |
#    # yes: wrong
#    When I fill in the following:
#      |  odr_action_gift_actionGiveGiftsToClient_0 | yes |
#      |  odr_action_gift_actionGiveGiftsToClientDetails |  |
#    And I press "odr_action_gift_save"
#    Then the following fields should have an error:
#      | odr_action_gift_actionGiveGiftsToClientDetails |
#    # yes
#    When I fill in the following:
#      |  odr_action_gift_actionGiveGiftsToClient_0 | yes |
#      |  odr_action_gift_actionGiveGiftsToClientDetails | ggtcd |
#    And I press "odr_action_gift_save"
#    Then the form should be valid
#    # check
#    When I click on "action-gifts"
#    Then the following fields should have the corresponding values:
#      |  odr_action_gift_actionGiveGiftsToClient_0 | yes |
#      |  odr_action_gift_actionGiveGiftsToClientDetails | ggtcd |
#    And I save the page as "odr-actions-gifts-done"