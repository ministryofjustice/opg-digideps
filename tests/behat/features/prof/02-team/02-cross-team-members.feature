Feature: PROF cross team members

  Scenario: Assert "behat-prof1-team-member" (TEAM1) has 18 clients
    Given I load the application status from "prof-team-users-complete"
    And I am logged in as "behat-prof1-team-member@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I go to "/pa/?limit=50"
    Then I should see the "client" region exactly 18 times
    And I should see the "client-01000010" region

  Scenario: TEAM3 named deputy adds used "behat-prof1-team-member" (TEAM1)
    Given I am logged in as "behat-prof3@publicguardian.gsi.gov.uk" with password "Abcd1234"
    # assert I see 3 clients from team3
    When I go to "/pa/?limit=50"
    Then I should see the "client" region exactly 3 times
    Then I should see the "client-03000001" region
    Then I should see the "client-03000002" region
    Then I should see the "client-03000003" region
    # members page
    And I click on "org-settings, user-accounts"
    Then I should see the "team-user-behat-prof3-team-memberpublicguardiangsigovuk" region
    When I click on "add"
    # add member already existing in the team => no extra behaviour (as cannot detect which team)
    And I fill in the following:
      | team_member_account_firstname  | PROF3                                             |
      | team_member_account_lastname   | Team Member                                       |
      | team_member_account_email      | behat-prof3-team-member@publicguardian.gsi.gov.uk |
      | team_member_account_roleName_1 | ROLE_PROF_TEAM_MEMBER                             |
    And I press "team_member_account_save"
    Then the form should be valid
    And I should see the "team-user-behat-prof3-team-memberpublicguardiangsigovuk" region
    # add member from team1
    When I click on "add"
    And I fill in the following:
      | team_member_account_firstname  | PROF3                                             |
      | team_member_account_lastname   | Team Member                                       |
      | team_member_account_email      | behat-prof1-team-member@publicguardian.gsi.gov.uk |
      | team_member_account_roleName_1 | ROLE_PROF_TEAM_MEMBER                             |
    And I press "team_member_account_save"
    Then the form should be valid
    And I should see the "team-user-behat-prof1-team-memberpublicguardiangsigovuk" region

  Scenario: "behat-prof1-team-member" can login and access deputies from TEAM1 and TEAM3
    Given I am logged in as "behat-prof1-team-member@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then the form should be valid
     # assert I now see 18+3 clients
    When I go to "/pa/?limit=50"
    Then I should see the "client" region exactly 21 times
    And I should see the "client-01000010" region
    And I should see the "client-03000001" region

  Scenario: "behat-prof1-team-member" removed from the team
    Given I am logged in as "behat-prof3@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "org-settings, user-accounts"
    And I click on "delete" in the "team-user-behat-prof1-team-memberpublicguardiangsigovuk" region
    And I click on "confirm"
    Then the response status code should be 200
    Then I should not see the "team-user-behat-prof1-team-memberpublicguardiangsigovuk" region


  Scenario: new team member can still access TEAM1, but not TEAM3 clients
    Given I am logged in as "behat-prof1-team-member@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I go to "/pa/?limit=50"
    Then I should see the "client" region exactly 18 times



