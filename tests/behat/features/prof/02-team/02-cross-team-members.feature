Feature: PROF cross team members

  Scenario: TEAM3 named deputy adds one users from TEAM1
    Given I load the application status from "prof-team-users-complete"
    And I am logged in as "behat-prof3@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "org-settings, user-accounts, add"
    # add member already existing in the team
    And I fill in the following:
      | team_member_account_firstname  | PROF3                                             |
      | team_member_account_lastname   | Team Member                                       |
      | team_member_account_email      | behat-prof3-team-member@publicguardian.gsi.gov.uk |
      | team_member_account_roleName_1 | ROLE_PROF_TEAM_MEMBER                             |
    And I press "team_member_account_save"
    Then the following fields should have an error:
      | team_member_account_email |
    #
    # add member from team1
    When I fill in the following:
      | team_member_account_firstname  | PROF3                                             |
      | team_member_account_lastname   | Team Member                                       |
      | team_member_account_email      | behat-prof1-team-member@publicguardian.gsi.gov.uk |
      | team_member_account_roleName_1 | ROLE_PROF_TEAM_MEMBER                             |
    And I press "team_member_account_save"
    Then the form should be valid
    And I should see the "team-user-behat-prof1-team-memberpublicguardiangsigovuk" region

  Scenario: new team member can login and access deputies from TEAM1 and TEAM3
    Given I am logged in as "behat-prof1-team-member@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then the form should be valid
    # //TODO

  Scenario: TEAM3 named deputy removes that new member from the team
    And I am logged in as "behat-prof3@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "org-settings, user-accounts"
    And I click on "delete" in the "team-user-behat-prof1-team-memberpublicguardiangsigovuk" region
    And I click on "confirm"
    Then the response status code should be 200
    Then I should not see the "team-user-behat-prof1-team-memberpublicguardiangsigovuk" region

  Scenario: new team member can still access TEAM1
    Given I am logged in as "behat-prof1-team-member@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then the form should be valid

