Feature: odr / assets

  @odr
  Scenario: ODR add asset vechicle
    Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "odr-start, edit-assets, assets-tab"
    And I save the page as "odr-assets-empty"
    # wrong form
    When I follow "add-assets-button"
    And I press "odr_asset_title_next"
    And I save the page as "odr-assets-title-add-error-empty"
    Then the following fields should have an error:
      | odr_asset_title_title |
    When I fill in the following:
      | odr_asset_title_title | Vehicles |
    And I press "odr_asset_title_next"
    Then the form should be valid
    And I save the page as "odr-assets-title-added"
    # rest of the form
    When I press "odr_asset_save"
    Then the following fields should have an error:
      | odr_asset_value       |
      | odr_asset_description |
    When I fill in the following:
      | odr_asset_value               | 1000000000001      |
      | odr_asset_description         | Alfa Romeo 156 JTD |
      | odr_asset_valuationDate_day   | 99                 |
      | odr_asset_valuationDate_month |                    |
      | odr_asset_valuationDate_year  | 2016               |
    And I press "odr_asset_save"
    And I save the page as "odr-assets-add-error-date"
    Then the following fields should have an error:
      | odr_asset_value               |
      | odr_asset_valuationDate_day   |
      | odr_asset_valuationDate_month |
      | odr_asset_valuationDate_year  |
   # correct
    When I fill in the following:
      | odr_asset_value               | 25000.00           |
      | odr_asset_description         | Alfa Romeo 156 JTD |
      | odr_asset_valuationDate_day   | 10                 |
      | odr_asset_valuationDate_month | 11                 |
      | odr_asset_valuationDate_year  | 2014               |
    And I press "odr_asset_save"
    Then the form should be valid
   # assert listing
    And I should see "Alfa Romeo 156 JTD" in the "list-assets" region
    And I should see "£25,000.00" in the "list-assets" region
    And I save the page as "odr-assets-list"

  @odr
  Scenario: ODR add, edit, delete asset
    Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "odr-start, edit-assets, assets-tab"
    When I follow "add-assets-button"
    # asset title
    And I press "odr_asset_title_next"
    And I fill in the following:
      | odr_asset_title_title | Vehicles |
    And I press "odr_asset_title_next"
    # asset full form
    When I fill in the following:
      | odr_asset_value               | 28000.01           |
      | odr_asset_description         | Mini GT 2000       |
      | odr_asset_valuationDate_day   | 10                 |
      | odr_asset_valuationDate_month | 11                 |
      | odr_asset_valuationDate_year  | 2013               |
    And I press "odr_asset_save"
    Then the form should be valid
    # assert listing
    And I should see "Mini GT 200" in the "list-assets" region
    # edit
    When I click on "asset-mini-gt-2000"
    Then the following fields should have the corresponding values:
      | odr_asset_value               | 28,000.01           |
      | odr_asset_description         | Mini GT 2000       |
      | odr_asset_valuationDate_day   | 10                 |
      | odr_asset_valuationDate_month | 11                 |
      | odr_asset_valuationDate_year  | 2013               |
    When I fill in the following:
      | odr_asset_description         | Mini GT 2001       |
    And I press "odr_asset_save"
    Then I should see "Mini GT 2001" in the "list-assets" region
    # delete
    When I click on "remove-asset-mini-gt-2001"
    And I should not see "Mini GT 2001"


  @odr
  Scenario: ODR add asset property
    Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "odr-start, edit-assets, assets-tab"
      # wrong form
    When I follow "add-assets-button"
    And I fill in the following:
      | odr_asset_title_title | Property |
    And I press "odr_asset_title_next"
    Then the form should be valid
    And I save the page as "odr-assets-property-title-added"
    # rest of the form
    When I press "odr_asset_save"
    Then the following fields should have an error:
      | odr_asset_address                    |
      | odr_asset_postcode                   |
      | odr_asset_occupants                  |
      | odr_asset_owned_0                    |
      | odr_asset_owned_1                    |
      | odr_asset_isSubjectToEquityRelease_0 |
      | odr_asset_isSubjectToEquityRelease_1 |
      | odr_asset_value                      |
      | odr_asset_hasMortgage_0              |
      | odr_asset_hasMortgage_1              |
      | odr_asset_hasCharges_0               |
      | odr_asset_hasCharges_1               |
      | odr_asset_isRentedOut_0              |
      | odr_asset_isRentedOut_1              |
      # secondary fields validation
    When I fill in the following:
      | odr_asset_address                    | 12 gold house        |
      | odr_asset_address2                   | mortimer road        |
      | odr_asset_county                     | westminster          |
      | odr_asset_postcode                   | sw115tf              |
      | odr_asset_occupants                  | only the deputy only |
      | odr_asset_owned_1                    | partly               |
      | odr_asset_isSubjectToEquityRelease_0 | yes                  |
      | odr_asset_value                      | 560000               |
      | odr_asset_hasMortgage_0              | yes                  |
      | odr_asset_hasCharges_1               | no                   |
      | odr_asset_isRentedOut_0              | yes                  |
    And I press "odr_asset_save"
    Then the following fields should have an error:
      | odr_asset_ownedPercentage            |
      | odr_asset_mortgageOutstandingAmount  |
      | odr_asset_rentIncomeMonth            |
      | odr_asset_rentAgreementEndDate_day   |
      | odr_asset_rentAgreementEndDate_month |
      | odr_asset_rentAgreementEndDate_year  |
      # no errors
    When I fill in the following:
      | odr_asset_ownedPercentage            | 45      |
      | odr_asset_mortgageOutstandingAmount  | 187500  |
      | odr_asset_rentAgreementEndDate_month | 12      |
      | odr_asset_rentAgreementEndDate_year  | 2017    |
      | odr_asset_rentIncomeMonth            | 1400.50 |
    And I press "odr_asset_save"
    Then the form should be valid
    And the response status code should be 200
    And I should see "12 gold house" in the "list-assets" region
    And I save the page as "odr-assets-property-list"
