Feature: deputy / report / asset with variations

    @deputy
    Scenario: assets
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports,report-2016-open, edit-assets, start"
        # chose "no records"
        Then the step cannot be submitted without making a selection
        And the step with the following values CAN be submitted:
            | asset_exist_noAssetToAdd_1 | 1 |
        # summary page check
        And each text should be present in the corresponding region:
            | No      | has-assets      |
        # select there are records (from summary page link)
        Given I click on "edit" in the "has-assets" region
        And the step with the following values CAN be submitted:
            | asset_exist_noAssetToAdd_0 | 0 |
        # add asset n.1 Vehicle (and validate form)
        Then the step cannot be submitted without making a selection
        And the step with the following values CAN be submitted:
            | asset_title_title_0 | Vehicles  |
        And the step with the following values CANNOT be submitted:
            | asset_value |   | [ERR] |
            | asset_description |   | [ERR] |
        And the step with the following values CANNOT be submitted:
            | asset_value       | 1000000000001 |  [ERR] |
            | asset_description | Alfa Romeo 156 JTD |  [OK] |
            | asset_valuationDate_day | 99 |  [ERR] |
            | asset_valuationDate_month |  |  [ERR] |
            | asset_valuationDate_year | 2016 |  [ERR] |
        And the step with the following values CAN be submitted:
            | asset_value       | 17,000 |
            | asset_description | Alfa Romeo 156 JTD |
            | asset_valuationDate_day | 12 |
            | asset_valuationDate_month | 1 |
            | asset_valuationDate_year | 2016 |
        # add asset n.2 Artwork
        And I choose "yes" when asked for adding another record
        And the step with the following values CAN be submitted:
            | asset_title_title_0 | Artwork |
        And the step with the following values CAN be submitted:
            | asset_value       | 25010.00 |
            | asset_description | Impressionist painting |
            | asset_valuationDate_day |  |
            | asset_valuationDate_month |  |
            | asset_valuationDate_year |  |
        # add asset n.3 Artwork (will be deleted)
        And I choose "yes" when asked for adding another record
        And the step with the following values CAN be submitted:
            | asset_title_title_0 | Artwork |
        And the step with the following values CAN be submitted:
            | asset_value       | 999.00 |
            | asset_description | temp |
            | asset_valuationDate_day |  |
            | asset_valuationDate_month |  |
            | asset_valuationDate_year |  |
        #add another: no
        And I choose "no" when asked for adding another record
        # check record in summary page
        And each text should be present in the corresponding region:
            | Alfa Romeo 156 JTD | asset-alfa-romeo-156-jtd |
            | £17,000.00 | asset-alfa-romeo-156-jtd |
            | 12 January 2016 | asset-alfa-romeo-156-jtd |
            | Impressionist painting | asset-impressionist-painting |
            | £25,010.00 | asset-impressionist-painting |
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
            | asset_value       | 17,000.00 |
            | asset_description | Alfa Romeo 156 JTD |
            | asset_valuationDate_day | 12 |
            | asset_valuationDate_month | 01 |
            | asset_valuationDate_year | 2016 |
        And the step with the following values CAN be submitted:
            | asset_value       | 17,500 |
            | asset_description | Alfa Romeo 147 JTD |
            | asset_valuationDate_day | 11 |
            | asset_valuationDate_month | 3 |
            | asset_valuationDate_year | 2015 |
        And each text should be present in the corresponding region:
            | Alfa Romeo 147 JTD | asset-alfa-romeo-147-jtd |
            | £17,500.00 | asset-alfa-romeo-147-jtd |
            | 11 March 2015 | asset-alfa-romeo-147-jtd |

    @deputy
    Scenario: properties
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports,report-2016-open, edit-assets, add"
        And the step with the following values CAN be submitted:
            | asset_title_title_0 | Property   |
        And the step with the following values CANNOT be submitted:
            | asset_address     |   | [ERR] |
            | asset_address2    |   |       |
            | asset_county      |   |       |
            | asset_postcode    |   | [ERR] |
        And the step with the following values CAN be submitted:
            | asset_address     | 12 gold house  |
            | asset_address2    | mortimer road  |
            | asset_county      |  westminster   |
            | asset_postcode    |  SW11 5TF      |
        Then the step cannot be submitted without making a selection
        And the step with the following values CAN be submitted:
            | asset_occupants | only the deputy only |
        Then the step cannot be submitted without making a selection
        And the step with the following values CANNOT be submitted:
            | asset_owned_1         | partly |       |
            | asset_ownedPercentage |        | [ERR] |
        And the step with the following values CAN be submitted:
            | asset_owned_1         | partly |
            | asset_ownedPercentage | 50     |
        Then the step cannot be submitted without making a selection
        And the step with the following values CANNOT be submitted:
            | asset_hasMortgage_0             | yes |       |
            | asset_mortgageOutstandingAmount |     | [ERR] |
        And the step with the following values CAN be submitted:
            | asset_hasMortgage_0             | yes    |
            | asset_mortgageOutstandingAmount | 120500 |
        Then the step cannot be submitted without making a selection
        And the step with the following values CAN be submitted:
            | asset_value             | 241000    |
        Then the step cannot be submitted without making a selection
        And the step with the following values CAN be submitted:
            | asset_isSubjectToEquityRelease_0 | yes  |
        Then the step cannot be submitted without making a selection
        And the step with the following values CAN be submitted:
            | asset_hasCharges_1 |  no |
        Then the step cannot be submitted without making a selection
        And the step with the following values CANNOT be submitted:
            | asset_isRentedOut_0             | yes |       |
            | asset_rentAgreementEndDate_month |     | [ERR] |
            | asset_rentAgreementEndDate_year |     | [ERR] |
        And the step with the following values CAN be submitted:
            | asset_isRentedOut_0               | yes     |
            | asset_rentAgreementEndDate_month  |  12     |
            | asset_rentAgreementEndDate_year   |  2017   |
            | asset_rentIncomeMonth             |  1350   |
        #add another: no
        And I choose "no" when asked for adding another record
        # check record in summary page
        And each text should be present in the corresponding region:
            | 12 gold house | property-sw11-5tf-address |
            | SW11 5TF | property-sw11-5tf-address |
        # edit asset n.1
        When I click on "edit" in the "property-sw11-5tf-address" region
        Then the following fields should have the corresponding values:
            | asset_address     | 12 gold house  |
            | asset_address2    | mortimer road  |
            | asset_county      |  westminster   |
            | asset_postcode    |  SW11 5TF      |
        And the step with the following values CAN be submitted:
            | asset_address     | 13 gold house  |
            | asset_postcode    |  SW11 6TF      |
        And each text should be present in the corresponding region:
            | 13 gold house | property-sw11-6tf-address |
        # remove property
        When I save the application status into "report-assets-finished"
        And I click on "delete" in the "property-sw11-6tf" region
        Then I should not see the "property-sw11-6tf" region
        And I load the application status from "report-assets-finished"
