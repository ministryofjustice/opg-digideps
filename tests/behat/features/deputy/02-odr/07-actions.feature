Feature: odr / actions / gifts

  @odr
  Scenario: ODR actions gifts
    Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "odr-start, edit-actions, start"
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

  #    And I click on "odr-start, edit-actions, action-property"
#    And I save the page as "odr-actions-property-empty"
#     # empty form
#    When I press "odr_action_property_save"
#    Then the following fields should have an error:
#      | odr_action_property_actionPropertyMaintenance_0 |
#      | odr_action_property_actionPropertyMaintenance_1 |
#      | odr_action_property_actionPropertySellingRent_0 |
#      | odr_action_property_actionPropertySellingRent_1 |
#      | odr_action_property_actionPropertyBuy_0 |
#      | odr_action_property_actionPropertyBuy_1 |
#    # no
#    When I fill in the following:
#      | odr_action_property_actionPropertyMaintenance_1 | no |
#      | odr_action_property_actionPropertySellingRent_1 | no |
#      | odr_action_property_actionPropertyBuy_1 | no |
#    And I press "odr_action_property_save"
#    Then the form should be valid
#    When I click on "action-property"
#    Then the following fields should have the corresponding values:
#      | odr_action_property_actionPropertyMaintenance_1 | no |
#      | odr_action_property_actionPropertySellingRent_1 | no |
#      | odr_action_property_actionPropertyBuy_1 | no |
#    # yes
#    When I fill in the following:
#      | odr_action_property_actionPropertyMaintenance_0 | yes |
#      | odr_action_property_actionPropertySellingRent_0 | yes |
#      | odr_action_property_actionPropertyBuy_0 | yes |
#    And I press "odr_action_property_save"
#    Then the form should be valid
#    When I click on "action-property"
#    Then the following fields should have the corresponding values:
#      | odr_action_property_actionPropertyMaintenance_0 | yes |
#      | odr_action_property_actionPropertySellingRent_0 | yes |
#      | odr_action_property_actionPropertyBuy_0 | yes |

  #    And I click on "odr-start, edit-actions, action-info"
#    And I save the page as "odr-actions-info-empty"
#     # empty form
#    When I press "odr_action_info_save"
#    Then the following fields should have an error:
#      | odr_action_info_actionMoreInfo_0 |
#      | odr_action_info_actionMoreInfo_1 |
#    # no
#    When I fill in the following:
#      |  odr_action_info_actionMoreInfo_1 | no |
#    And I press "odr_action_info_save"
#    Then the form should be valid
#    When I click on "action-info"
#    Then the following fields should have the corresponding values:
#      |  odr_action_info_actionMoreInfo_1 | no |
#    # yes: wrong
#    When I fill in the following:
#      |  odr_action_info_actionMoreInfo_0 | yes |
#      |  odr_action_info_actionMoreInfoDetails |  |
#    And I press "odr_action_info_save"
#    Then the following fields should have an error:
#      | odr_action_info_actionMoreInfoDetails |
#    # yes
#    When I fill in the following:
#      |  odr_action_info_actionMoreInfo_0 | yes |
#      |  odr_action_info_actionMoreInfoDetails | amid |
#    And I press "odr_action_info_save"
#    Then the form should be valid
#    # check
#    When I click on "action-info"
#    Then the following fields should have the corresponding values:
#      |  odr_action_info_actionMoreInfo_0 | yes |
#      |  odr_action_info_actionMoreInfoDetails | amid |
#    And I save the page as "odr-actions-info-done"