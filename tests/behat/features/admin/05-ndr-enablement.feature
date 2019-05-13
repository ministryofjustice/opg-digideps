Feature: Enabling and disabling NDR for Lay deputies

  Scenario: Enabling and disabling NDR from admin toggles the availability of the NDR and Report accordingly
    Given emails are sent from "admin" area
    And I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And there is an activated "Lay Deputy" user with NDR "disabled" and email "red-squirrel@publicguardian.gov.uk" and password "Abcd1234"
    And I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"

    # Enabling NDR makes NDR available and disables the Report
    When I "enable" NDR for user "red-squirrelpublicguardiangovuk"
    And I am logged in as "red-squirrel@publicguardian.gov.uk" with password "Abcd1234"
    Then I should see "Start now" in the "ndr-card" region
    And I should see "Not available" in the "report-card" region
    # (Store the NDR URL to retrieve for upcoming test)
    When I click on "ndr-start"
    Then I save the report as "red-squirrel-ndr"

    # Disabling NDR makes NDR unavailable and enables the Report
    When I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I "disable" NDR for user "red-squirrelpublicguardiangovuk"
    And I am logged in as "red-squirrel@publicguardian.gov.uk" with password "Abcd1234"
    Then I should see "Start now" in the "report-active" region

    # Re-enabling NDR retrieves the previous NDR (instead of creating a new one)
    When I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I "enable" NDR for user "red-squirrelpublicguardiangovuk"
    And I am logged in as "red-squirrel@publicguardian.gov.uk" with password "Abcd1234"
    Then I should see "Start now" in the "ndr-card" region
    And I should see "Not available" in the "report-card" region
    When I go to the report URL "overview" for "red-squirrel-ndr"
    Then the response status code should be 200
