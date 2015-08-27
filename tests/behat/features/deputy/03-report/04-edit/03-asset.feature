Feature: deputy / report / edit asset


    @deputy
    Scenario: edit asset-remove
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "client-home"
        And I click on "report-n2"
        And I follow "tab-assets"
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
       And I save the page as "report-assets-remove-edit"
       And I click on "delete-confirm"
       And I save the page as "report-assets-remove-delete-confirm"
       And I click on "delete-confirm-cancel"
       And I save the page as "report-assets-remove-remove-cancel"
       And I click on "delete-confirm"
       And I click on "delete"
       And I save the page as "report-assets-remove-remove-deleted"
       And the URL should match "/report/\d+/assets"
       Then I should not see "I love my artworks" in the "list-assets" region
       And I should not see the "asset-i-love-my-artworks" link
