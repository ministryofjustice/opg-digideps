Feature: Clients can be moved from one PA to another

  Scenario: PA reload status from the point where team1 has been fully added
    Given I load the application status from "team-users-complete"

  Scenario: Move clients to another PA in Admin
    Given I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
      # upload PA users
    When I click on "admin-upload-pa"
    When I attach the file "behat-pa-moved.csv" to "admin_upload_file"
    And I press "admin_upload_upload"
    Then the form should be valid

  Scenario: PA3 can see moved clients
    # can access team2 reports
    Given I am logged in as "behat-pa3@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then I should see the "client" region exactly 8 times
    And I should see the "client-3000001" region
    And I should see the "client-1000014" region
    When I am logged in as "behat-pa3-admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then I should see the "client" region exactly 8 times
    And I should see the "client-3000001" region
    And I should see the "client-1000014" region
    When I am logged in as "behat-pa3-team-member@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then I should see the "client" region exactly 8 times
    And I should see the "client-3000001" region
    And I should see the "client-1000014" region

  Scenario: PA1 users cannot see moved clients
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then I should see the "client" region exactly 13 times
    And I should see the "client-1000010" region
    And I should not see the "client-1000014" region
    When I am logged in as "behat-pa1-admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then I should see the "client" region exactly 13 times
    And I should see the "client-1000010" region
    And I should not see the "client-1000014" region
    When I am logged in as "behat-pa1-team-member@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then I should see the "client" region exactly 13 times
    And I should see the "client-1000010" region
    And I should not see the "client-1000014" region
