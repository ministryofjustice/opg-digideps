Feature: deputy / report / edit asset

    @deputy
    Scenario: edit and remove other asset
        Given I load the application status from "report-submit-pre"
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports, report-2016-open, edit-assets"
        And I save the page as "report-assets-other-init"
        And I click on "asset-alfa-romeo-156-jtd"
        Then the following fields should have the corresponding values:
            | asset_value | 13,000.00 |
            | asset_description | Alfa Romeo 156 JTD |
            | asset_valuationDate_day | 10 |
            | asset_valuationDate_month | 11 |
            | asset_valuationDate_year | 2016 |
        And I click on "cancel-edit"
        And the URL should match "/report/\d+/assets"
        And I click on "asset-alfa-romeo-156-jtd"
        When I fill in the following:
            | asset_value | 10,000.00 |
            | asset_description | I love my artworks |
            | asset_valuationDate_day | 11 |
            | asset_valuationDate_month | 11 |
            | asset_valuationDate_year | 2016 |
       And I press "asset_save"
       Then I should see "I love my artworks" in the "list-assets" region
       And I should see "£10,000.00" in the "list-assets" region
       And I click on "asset-i-love-my-artworks"
       And I click on "delete-button"
       And the URL should match "/report/\d+/assets"
       Then I should not see "I love my artworks" in the "list-assets" region
       And I should not see the "asset-i-love-my-artworks" link
    
    @deputy
    Scenario: edit and remove property asset
        Given I load the application status from "report-submit-pre"
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports,report-2016-open,edit-assets"
        And I save the page as "report-assets-property-init"
        And I click on "asset-12-gold-house-sw115tf"
        Then the following fields should have the corresponding values:
            | asset_address | 12 gold house  |
            | asset_address2 | mortimer road  |
            | asset_county |  westminster |
            | asset_postcode |  sw115tf  |
            | asset_occupants | only the deputy only  |
            | asset_owned_1 | partly |
            | asset_ownedPercentage | 45 |
            | asset_isSubjectToEquityRelease_0 | yes  |
            | asset_value | 560,000.00  |
            | asset_hasMortgage_0 | yes  |
            | asset_mortgageOutstandingAmount | 187,500.00 |
            | asset_hasCharges_1 |  no |
            | asset_isRentedOut_0 | yes  |
            | asset_rentAgreementEndDate_month | 12 |
            | asset_rentAgreementEndDate_year | 2017 |
            | asset_rentIncomeMonth | 1,400.50 |
        And I click on "cancel-edit"
        And the URL should match "/report/\d+/assets"
        And I click on "asset-alfa-romeo-156-jtd"
        When I fill in the following:
            | asset_value | 10,000.00 |
            | asset_description | I love my artworks |
            | asset_valuationDate_day | 11 |
            | asset_valuationDate_month | 11 |
            | asset_valuationDate_year | 2016 |
       And I press "asset_save"
       Then I should see "I love my artworks" in the "list-assets" region
       And I should see "£10,000.00" in the "list-assets" region
       And I click on "asset-i-love-my-artworks"
       And I click on "delete-button"
       And the URL should match "/report/\d+/assets"
       Then I should not see "I love my artworks" in the "list-assets" region
       And I should not see the "asset-i-love-my-artworks" link


    @deputy
    Scenario: add explanation for no assets
      Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
      And I click on "reports, report-2016-open"
      # delete current asset
      And I follow "edit-assets"
      And I click on "asset-12-gold-house-sw115tf"
      And I click on "delete-button"
      And I click on "asset-impressionist-painting"
      And I click on "delete-button"
      Then the checkbox "report_noAssetToAdd" should be unchecked
      And I save the page as "report-no-asset-empty"
      # submit without ticking the box
      And I press "report_saveNoAsset"
      Then the form should be invalid
      And I save the page as "report-no-asset-error"
      # tick and submit
      When I check "report_noAssetToAdd"
      And I press "report_saveNoAsset"
      Then the form should be valid
      And I save the page as "report-no-asset-added"
      And I should see the "no-assets-selected" region
      # add asset 
      When I add the following assets:
        | title        | value       |  description        | valuationDate | 
        | Vehicles    | 13000.00    |  Alfa Romeo 156 JTD | 10/11/2016 |
      And I click on "asset-alfa-romeo-156-jtd"
      And I click on "delete-button"
      # check checkbox is reset
      Then the checkbox "report_noAssetToAdd" should be unchecked
