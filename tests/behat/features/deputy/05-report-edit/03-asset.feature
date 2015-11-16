Feature: deputy / report / edit asset

    @deputy
    Scenario: edit asset-remove
        Given I load the application status from "report-submit-pre"
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "client-home"
        And I click on "report-2015"
        And I follow "edit-assets"
        And I save the page as "report-assets-remove-init"
        And I click on "asset-alfa-romeo-156-jtd"
        Then the following fields should have the corresponding values:
            | asset_value | 13,000.00 |
            | asset_description | Alfa Romeo 156 JTD |
            | asset_valuationDate_day | 10 |
            | asset_valuationDate_month | 11 |
            | asset_valuationDate_year | 2015 |
        And I click on "cancel-edit"
        And the URL should match "/report/\d+/assets"
        And I click on "asset-alfa-romeo-156-jtd"
        When I fill in the following:
            | asset_value | 10,000.00 |
            | asset_description | I love my artworks |
            | asset_valuationDate_day | 11 |
            | asset_valuationDate_month | 11 |
            | asset_valuationDate_year | 2015 |
       And I press "asset_save"
       And I save the page as "report-assets-remove-added"
       Then I should see "I love my artworks" in the "list-assets" region
       And I should see "£10,000.00" in the "list-assets" region
       And I click on "asset-i-love-my-artworks"
       And I click on "delete-button"
       And I save the page as "report-assets-remove-remove-deleted"
       And the URL should match "/report/\d+/assets"
       Then I should not see "I love my artworks" in the "list-assets" region
       And I should not see the "asset-i-love-my-artworks" link
    
    @deputy
    Scenario: add explanation for no assets
      Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
      #And I am on the "2015" report overview page
      And I click on "client-home"
      And I click on "report-2015"
      # delete current asset
      And I follow "edit-assets"
      And I click on "asset-2-beds-flat-in-ha2"
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
        | Vehicles    | 13000.00    |  Alfa Romeo 156 JTD | 10/11/2015 |
      And I click on "asset-alfa-romeo-156-jtd"
      And I click on "delete-button"
      # check checkbox is reset
      Then the checkbox "report_noAssetToAdd" should be unchecked
