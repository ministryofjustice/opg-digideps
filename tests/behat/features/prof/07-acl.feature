Feature: PROF cannot access other's PROF's reports and clients
# team1 = team with client 1000010
# team2 = team with client 2000003

  Scenario: PROF reload status from the point where team1 has been fully added
    Given I load the application status from "prof-team-users-complete"

  Scenario: Assert team1 can only access its reports
    # Named PROF
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-01000010" region
    Then the response status code should be 200
    And the URL should match "report/\d+/overview"
    And I save the current URL as "report-for-client-01000010.url"
    But I should not see the "client-02000003" region
    # Admin
    Given I am logged in as "behat-prof1-admin@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-01000010" region
    Then the response status code should be 200
    And the current URL should match with the URL previously saved as "report-for-client-01000010.url"
    But I should not see the "client-02000003" region
    # team member
    Given I am logged in as "behat-prof1-team-member@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-01000010" region
    Then the response status code should be 200
    And the current URL should match with the URL previously saved as "report-for-client-01000010.url"
    But I should not see the "client-02000003" region

  Scenario: team2 can access its client but not team1's data
    # can access team2 reports
    Given I am logged in as "behat-prof2@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-02000001" region
    Then the response status code should be 200
    And the URL should match "report/\d+/overview"
    # cannot access team1 reports
    But I should not see the "client-01000010" region
    When I go to the URL previously saved as "report-for-client-01000010.url"
    Then the response status code should be 500

  Scenario: PROF user cannot edit client
    Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    Then the URL "/deputyship-details" should be forbidden
    And the URL "/deputyship-details/your-client" should be forbidden
    And the URL "/deputyship-details/your-client/edit" should be forbidden
    And the URL "/deputyship-details/your-details" should be forbidden
    And the URL "/deputyship-details/your-details/edit" should be forbidden
    And the URL "/deputyship-details/your-details/change-password" should be forbidden

#  Scenario: Submitted reports cannot be viewed (overview page) or edited
#    # load "pre-submission" status and save links
#    Given I load the application status from "prof-report-completed"
#    And I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
#    When I click on "pa-report-open" in the "client-01000014" region
#    And I save the current URL as "client-01000014-report-overview"
#    And I click on "edit-report-period"
#    Then the response status code should be 200
#    # load "after submission" status and re-check the same links
#    And I save the current URL as "client-01000014-report-completed"
#    When I load the application status from "prof-report-submitted"
#    When I go to the URL previously saved as "client-01000014-report-overview"
#    Then the response status code should be 500
#    When I go to the URL previously saved as "client-01000014-report-completed"
#    Then the response status code should be 500

  Scenario: PROF_ADMIN logs in, edits own account and removes admin privilege should be logged out
    Given I load the application status from "prof-team-users-complete"
    And I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "org-settings, user-accounts"
    When I click on "edit" in the "team-user-behat-prof1-adminpublicguardiangovuk" region
    And I fill in the following:
      | team_member_account_roleName_1 | ROLE_PROF_TEAM_MEMBER                             |
    And I press "team_member_account_save"
    Then the form should be valid
    And the response status code should be 200
    And I go to "/logout"

  Scenario: PROF_ADMIN logs in, edits own account keeps admin privilege should remain logged in
    Given I load the application status from "prof-team-users-complete"
    And I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "org-settings, user-accounts"
    When I click on "edit" in the "team-user-behat-prof1-adminpublicguardiangovuk" region
    And I fill in the following:
      | team_member_account_firstname  | edit                                             |
    And I press "team_member_account_save"
    Then the form should be valid
    And the response status code should be 200
    And I go to "/org/team"
