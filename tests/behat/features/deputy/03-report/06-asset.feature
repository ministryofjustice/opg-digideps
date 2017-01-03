Feature: deputy / report / asset with variations

    @deputy
    Scenario: add asset
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
        # add asset n.1 (and validate form)
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
        # add asset n.2
        And I choose "yes" when asked for adding another record
        And the step with the following values CAN be submitted:
            | asset_title_title_0 | Artwork |
        And the step with the following values CAN be submitted:
            | asset_value       | 25010.00 |
            | asset_description | Impressionist painting |
            | asset_valuationDate_day |  |
            | asset_valuationDate_month |  |
            | asset_valuationDate_year |  |
        # add asset n.3
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
        When I click on "step-back"
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




#    @deputy
#    Scenario: add asset property
#        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        And I click on "reports,report-2016-open, edit-assets"
#        And I save the page as "report-assets-empty"
#        # wrong form
#        When I follow "add-assets-button"
#        Then I fill in "asset_title_title" with "Property"
#        And I press "asset_title_next"
#        Then the form should be valid
#        And I save the page as "report-assets-property-title-added"
#        # rest of the form
#        When I press "asset_save"
#        Then the following fields should have an error:
#            | asset_address |
#            | asset_postcode |
#            | asset_occupants |
#            | asset_owned_0 |
#            | asset_owned_1 |
#            | asset_isSubjectToEquityRelease_0 |
#            | asset_isSubjectToEquityRelease_1 |
#            | asset_value |
#            | asset_hasMortgage_0 |
#            | asset_hasMortgage_1 |
#            | asset_hasCharges_0 |
#            | asset_hasCharges_1 |
#            | asset_isRentedOut_0 |
#            | asset_isRentedOut_1 |
#        # secondary fields validation
#        When I fill in the following:
#            | asset_address | 12 gold house  |
#            | asset_address2 | mortimer road  |
#            | asset_county |  westminster |
#            | asset_postcode |  sw115tf  |
#            | asset_occupants | only the deputy only  |
#            | asset_owned_1 | partly |
#            | asset_isSubjectToEquityRelease_0 | yes  |
#            | asset_value | 560000  |
#            | asset_hasMortgage_0 | yes  |
#            | asset_hasCharges_1 |  no |
#            | asset_isRentedOut_0 | yes  |
#        And I press "asset_save"
#        Then the following fields should have an error:
#            | asset_ownedPercentage |
#            | asset_mortgageOutstandingAmount |
#            | asset_rentIncomeMonth |
#            | asset_rentAgreementEndDate_month |
#            | asset_rentAgreementEndDate_year |
#        # no errors
#        When I fill in the following:
#            | asset_ownedPercentage | 45 |
#            | asset_mortgageOutstandingAmount | 187500 |
#            | asset_rentAgreementEndDate_month | 12 |
#            | asset_rentAgreementEndDate_year | 2017 |
#            | asset_rentIncomeMonth | 1400.50 |
#        And I press "asset_save"
#        Then the form should be valid
#        And the response status code should be 200
#        And I should see "12 gold house" in the "list-assets" region
#        And I save the page as "report-assets-property-list"

