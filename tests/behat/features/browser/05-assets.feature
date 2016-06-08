Feature: browser - assets
    
    @browser
    Scenario: browser - Add an asset
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "edit-assets"
        And I save the page as "assets-none"
        Then follow "add-assets-button"
        And I fill in "asset_title_title" with "Artwork"
        And I save the page as "asset-add-1"
        And I pause
        And I save the page as "assets-add-2"
        Then I fill in the following:
            | asset_value               | 100 |
            | asset_description         | Test asset |
            | asset_valuationDate_day   | 1 |
            | asset_valuationDate_month | 1 |
            | asset_valuationDate_year  | 2014 |
        And I press "asset_save"
        And I pause
        Then I save the page as "assets-list"
        Then I should see "Test asset" in the "list-assets" region
        Given I click on "asset-test-asset"
        And I save the page as "asset-edit"
        And I click on "delete-button"
        And I save the page as "asset-delete-confirm"
        Then I should see a confirmation
        When I click on "delete-confirm"
        Then the URL should match "/report/\d+/assets"
        Then I should not see "Test asset"
        # Add it back in, to we can submit the report
        Then follow "add-assets-button"
        And I fill in "asset_title_title" with "Artwork"
        And I save the page as "asset-add-1"
        And I pause
        And I save the page as "assets-add-2"
        Then I fill in the following:
            | asset_value               | 100 |
            | asset_description         | Test asset |
            | asset_valuationDate_day   | 1 |
            | asset_valuationDate_month | 1 |
            | asset_valuationDate_year  | 2014 |
        And I press "asset_save"
