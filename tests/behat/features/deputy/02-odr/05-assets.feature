Feature: NDR assets

  @odr
  Scenario: NDR assets
    Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "odr-start, edit-assets, start"
      # chose "no records"
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_noAssetToAdd_1 | 1 |
      # summary page check
    And each text should be present in the corresponding region:
      | No | has-assets |
      # select there are records (from summary page link)
    Given I click on "edit" in the "has-assets" region
    And the step with the following values CAN be submitted:
      | yes_no_noAssetToAdd_0 | 0 |
      # add asset n.1 Vehicle (and validate form)
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | odr_asset_title_title_0 | Vehicles |
    And the step with the following values CANNOT be submitted:
      | odr_asset_value       |  | [ERR] |
      | odr_asset_description |  | [ERR] |
    And the step with the following values CANNOT be submitted:
      | odr_asset_value               | 1000000000001      | [ERR] |
      | odr_asset_description         | Alfa Romeo 156 JTD | [OK]  |
      | odr_asset_valuationDate_day   | 99                 | [ERR] |
      | odr_asset_valuationDate_month |                    | [ERR] |
      | odr_asset_valuationDate_year  | 2016               | [ERR] |
    And the step with the following values CAN be submitted:
      | odr_asset_value               | 17,000             |
      | odr_asset_description         | Alfa Romeo 156 JTD |
      | odr_asset_valuationDate_day   | 12                 |
      | odr_asset_valuationDate_month | 1                  |
      | odr_asset_valuationDate_year  | 2016               |
      # add asset n.2 Artwork
    And I choose "yes" when asked for adding another record
    And the step with the following values CAN be submitted:
      | odr_asset_title_title_0 | Artwork |
    And the step with the following values CAN be submitted:
      | odr_asset_value               | 25010.00               |
      | odr_asset_description         | Impressionist painting |
      | odr_asset_valuationDate_day   |                        |
      | odr_asset_valuationDate_month |                        |
      | odr_asset_valuationDate_year  |                        |
      # add asset n.3 Artwork (will be deleted)
    And I choose "yes" when asked for adding another record
    And the step with the following values CAN be submitted:
      | odr_asset_title_title_0 | Artwork |
    And the step with the following values CAN be submitted:
      | odr_asset_value               | 999.00 |
      | odr_asset_description         | temp   |
      | odr_asset_valuationDate_day   |        |
      | odr_asset_valuationDate_month |        |
      | odr_asset_valuationDate_year  |        |
      #add another: no
    And I choose "no" when asked for adding another record
      # check record in summary page
    And each text should be present in the corresponding region:
      | Alfa Romeo 156 JTD     | asset-alfa-romeo-156-jtd     |
      | £17,000.00             | asset-alfa-romeo-156-jtd     |
      | 12 January 2016        | asset-alfa-romeo-156-jtd     |
      | Impressionist painting | asset-impressionist-painting |
      | £25,010.00             | asset-impressionist-painting |
      # remove asset n.3
    When I click on "delete" in the "asset-temp" region
    Then I should not see the "asset-temp" region
      # test add link
    When I click on "add"
    Then I should see the "save-and-continue" link
    When I go back from the step
      # edit asset n.1
    When I click on "edit" in the "asset-alfa-romeo-156-jtd" region
    Then the following fields should have the corresponding values:
      | odr_asset_value               | 17,000.00          |
      | odr_asset_description         | Alfa Romeo 156 JTD |
      | odr_asset_valuationDate_day   | 12                 |
      | odr_asset_valuationDate_month | 01                 |
      | odr_asset_valuationDate_year  | 2016               |
    And the step with the following values CAN be submitted:
      | odr_asset_value               | 17,500             |
      | odr_asset_description         | Alfa Romeo 147 JTD |
      | odr_asset_valuationDate_day   | 11                 |
      | odr_asset_valuationDate_month | 3                  |
      | odr_asset_valuationDate_year  | 2015               |
    And each text should be present in the corresponding region:
      | Alfa Romeo 147 JTD | asset-alfa-romeo-147-jtd |
      | £17,500.00         | asset-alfa-romeo-147-jtd |
      | 11 March 2015      | asset-alfa-romeo-147-jtd |
      | £42,510.00         | asset-total              |


  @odr
  Scenario: NDR asset property
    Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "odr-start, edit-assets, add"
    And the step with the following values CAN be submitted:
      | odr_asset_title_title_0 | Property |
    And the step with the following values CANNOT be submitted:
      | odr_asset_address  |  | [ERR] |
      | odr_asset_address2 |  |       |
      | odr_asset_county   |  |       |
      | odr_asset_postcode |  | [ERR] |
    And the step with the following values CAN be submitted:
      | odr_asset_address  | 12 gold house |
      | odr_asset_address2 | mortimer road |
      | odr_asset_county   | westminster   |
      | odr_asset_postcode | SW11 5TF      |
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | odr_asset_occupants | only the deputy only |
    Then the step cannot be submitted without making a selection
    And the step with the following values CANNOT be submitted:
      | odr_asset_owned_1         | partly |       |
      | odr_asset_ownedPercentage |        | [ERR] |
    And the step with the following values CAN be submitted:
      | odr_asset_owned_1         | partly |
      | odr_asset_ownedPercentage | 50     |
    Then the step cannot be submitted without making a selection
    And the step with the following values CANNOT be submitted:
      | odr_asset_hasMortgage_0             | yes |       |
      | odr_asset_mortgageOutstandingAmount |     | [ERR] |
    And the step with the following values CAN be submitted:
      | odr_asset_hasMortgage_0             | yes    |
      | odr_asset_mortgageOutstandingAmount | 120500 |
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | odr_asset_value | 241000 |
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | odr_asset_isSubjectToEquityRelease_0 | yes |
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | odr_asset_hasCharges_1 | no |
    Then the step cannot be submitted without making a selection
    And the step with the following values CANNOT be submitted:
      | odr_asset_isRentedOut_0              | yes |       |
      | odr_asset_rentAgreementEndDate_month |     | [ERR] |
      | odr_asset_rentAgreementEndDate_year  |     | [ERR] |
    And the step with the following values CAN be submitted:
      | odr_asset_isRentedOut_0              | yes  |
      | odr_asset_rentAgreementEndDate_month | 12   |
      | odr_asset_rentAgreementEndDate_year  | 2017 |
      | odr_asset_rentIncomeMonth            | 1350 |
    #add another: no
    And I choose "no" when asked for adding another record
    # check record in summary page
    And each text should be present in the corresponding region:
      | 12 gold house | property-sw11-5tf-address |
      | SW11 5TF      | property-sw11-5tf-address |
    # edit asset n.1
    When I click on "edit" in the "property-sw11-5tf-address" region
    Then the following fields should have the corresponding values:
      | odr_asset_address  | 12 gold house |
      | odr_asset_address2 | mortimer road |
      | odr_asset_county   | westminster   |
      | odr_asset_postcode | SW11 5TF      |
    And the step with the following values CAN be submitted:
      | odr_asset_address  | 13 gold house |
      | odr_asset_postcode | SW11 6TF      |
    And each text should be present in the corresponding region:
      | 13 gold house | property-sw11-6tf-address |
    # remove property
    When I save the application status into "report-assets-finished"
    And I click on "delete" in the "property-sw11-6tf" region
    Then I should not see the "property-sw11-6tf" region
    And I load the application status from "report-assets-finished"
