@v2 @v2_reporting_1 @assets @aaa
Feature: Assets (NDR)

    @ndr-not-started
    Scenario: A user has no assets to add
        Given a Lay Deputy has not started an NDR report
        When I visit and start the assets report section
        And I confirm the client has no assets
        Then I should see the expected assets report section responses
        When I follow link back to report overview page
        Then I should see "assets" as "no assets"

    @ndr-not-started
    Scenario: A user adds a single asset
        Given a Lay Deputy has not started an NDR report
        When I visit and start the assets report section
        And I confirm the client has assets
        And I add 1 asset
        Then I should see the expected assets report section responses
        When I follow link back to report overview page
        Then I should see "assets" as "1 asset"

    @ndr-not-started
    Scenario: A user adds a single property asset
        Given a Lay Deputy has not started an NDR report
        When I visit and start the assets report section
        And I confirm the client has assets
        And I add 1 property asset
        Then I should see the expected assets report section responses
        When I follow link back to report overview page
        Then I should see "assets" as "1 asset"

    @ndr-not-started
    Scenario: A user adds multiple properties
        Given a Lay Deputy has not started an NDR report
        When I visit and start the assets report section
        And I confirm the client has assets
        And I add 3 property assets
        Then I should see the expected assets report section responses
        When I follow link back to report overview page
        Then I should see "assets" as "3 assets"

        @ndr-not-started
    Scenario: A user adds multiple assets and a property
        Given a Lay Deputy has not started an NDR report
        When I visit and start the assets report section
        And I confirm the client has assets
        And I add 12 assets
        And I add 1 property asset
        Then I should see the expected assets report section responses
        When I follow link back to report overview page
        Then I should see "assets" as "12 assets"
