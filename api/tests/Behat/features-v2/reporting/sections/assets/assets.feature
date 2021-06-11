@v2 @assets
Feature: Assets

@pfa-high-not-started
  Scenario: A user has no assets to add
    Given a Lay Deputy has not started a report
    When I visit and start the assets report section
    And I confirm the client has no assets
    Then I should see the expected assets report section responses
    When I follow link back to report overview page
    Then I should see "assets" as "no assets"

@pfa-high-not-started
  Scenario: A user adds a single asset
    Given a Lay Deputy has not started a report
    When I visit and start the assets report section
    And I confirm the client has assets
    And I add a single asset
    Then I should see the expected assets report section responses
    When I follow link back to report overview page
    Then I should see "assets" as "1 asset"

@pfa-high-not-started
  Scenario: A user adds a single property asset
    Given a Lay Deputy has not started a report
    When I visit and start the assets report section
    And I confirm the client has assets
    And I add a single property asset
    Then I should see the expected assets report section responses
    When I follow link back to report overview page
    Then I should see "assets" as "1 asset"

@pfa-high-not-started
  Scenario: A user adds multiple properties
    Given a Lay Deputy has not started a report
    When I visit and start the assets report section
    And I confirm the client has assets
    And I add 3 property assets
    Then I should see the expected assets report section responses
    When I follow link back to report overview page
    Then I should see "assets" as "3 assets"

@pfa-high-not-started
  Scenario: A user adds multiple assets and a property
    Given a Lay Deputy has not started a report
    When I visit and start the assets report section
    And I confirm the client has assets
    And I add 12 assets including a property
    Then I should see the expected assets report section responses
    When I follow link back to report overview page
    Then I should see "assets" as "12 assets"
