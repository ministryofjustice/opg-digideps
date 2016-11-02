Feature: deputy / report / asset with variations

    @deputy
    Scenario: add asset
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports,report-2016-open, edit-assets"
        And I save the page as "report-assets-empty"
        # wrong form
        When I follow "add-assets-button"
        And I press "asset_title_next"
        And I save the page as "report-assets-title-add-error-empty"
        Then the following fields should have an error:
            | asset_title_title |
        Then I fill in "asset_title_title" with "Vehicles"
        And I press "asset_title_next"
        Then the form should be valid
        And I save the page as "report-assets-title-added"
        # rest of the form
        When I press "asset_save"
          Then the following fields should have an error:
            | asset_value |
            | asset_description |
        When I fill in the following:
            | asset_value       | 1000000000001 |
            | asset_description | Alfa Romeo 156 JTD |
            | asset_valuationDate_day | 99 |
            | asset_valuationDate_month |  |
            | asset_valuationDate_year | 2016 |
        And I press "asset_save"
        And I save the page as "report-assets-add-error-date"
        Then the following fields should have an error:
            | asset_value |
            | asset_valuationDate_day |
            | asset_valuationDate_month |
            | asset_valuationDate_year |
        # first asset (empty date)
        Then the "asset_description" field should be expandable
        When I add the following assets:
          | title        | value       |  description        | valuationDate |
          | Artwork    | 250000.00   |  Impressionist painting  |               |
          | Vehicles    | 13000.00   |  Alfa Romeo 156 JTD |    10/11/2016  |
        And I should see " Impressionist painting" in the "list-assets" region
        And I should see "£250,000.00" in the "list-assets" region
        Then I should see "Alfa Romeo 156 JTD" in the "list-assets" region
        And I should see "£13,000.00" in the "list-assets" region
        And I save the page as "report-assets-list"
    
    @deputy
    Scenario: add asset property
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports,report-2016-open, edit-assets"
        And I save the page as "report-assets-empty"
        # wrong form
        When I follow "add-assets-button"
        Then I fill in "asset_title_title" with "Property"
        And I press "asset_title_next"
        Then the form should be valid
        And I save the page as "report-assets-property-title-added"
        # rest of the form
        When I press "asset_save"
        Then the following fields should have an error:
            | asset_address |
            | asset_postcode |
            | asset_occupants |
            | asset_owned_0 |
            | asset_owned_1 |
            | asset_isSubjectToEquityRelease_0 |
            | asset_isSubjectToEquityRelease_1 |
            | asset_value |
            | asset_hasMortgage_0 |
            | asset_hasMortgage_1 |
            | asset_hasCharges_0 |
            | asset_hasCharges_1 |
            | asset_isRentedOut_0 |
            | asset_isRentedOut_1 |
        # secondary fields validation    
        When I fill in the following:
            | asset_address | 12 gold house  |
            | asset_address2 | mortimer road  |
            | asset_county |  westminster |
            | asset_postcode |  sw115tf  |
            | asset_occupants | only the deputy only  |
            | asset_owned_1 | partly |
            | asset_isSubjectToEquityRelease_0 | yes  |
            | asset_value | 560000  |
            | asset_hasMortgage_0 | yes  |
            | asset_hasCharges_1 |  no |
            | asset_isRentedOut_0 | yes  |
        And I press "asset_save"
        Then the following fields should have an error:
            | asset_ownedPercentage |
            | asset_mortgageOutstandingAmount |
            | asset_rentIncomeMonth |
            | asset_rentAgreementEndDate_month |
            | asset_rentAgreementEndDate_year |
        # no errors
        When I fill in the following:
            | asset_ownedPercentage | 45 |
            | asset_mortgageOutstandingAmount | 187500 |
            | asset_rentAgreementEndDate_month | 12 |
            | asset_rentAgreementEndDate_year | 2017 |
            | asset_rentIncomeMonth | 1400.50 |
        And I press "asset_save"
        Then the form should be valid
        And the response status code should be 200
        And I should see "12 gold house" in the "list-assets" region
        And I save the page as "report-assets-property-list"    

