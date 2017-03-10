Feature: PA team

  Scenario: team page
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-settings"
    # settings page
    And I click on "user-accounts"
    Then I should see the "team-user-behat-pa1publicguardiangsigovuk" region

  Scenario: PA (named) logs in and adds PA_ADMIN user
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-settings, user-accounts"
    # add user - test form
    When I click on "add"
    And I press "team_member_account_save"
    Then the following fields should have an error:
      | team_member_account_firstname  |
      | team_member_account_lastname   |
      | team_member_account_email      |
      | team_member_account_roleName_0 |
      | team_member_account_roleName_1 |
    # add user ADMIN
    When I fill in the following:
      | team_member_account_firstname  | Markk Admin                               |
      | team_member_account_lastname   | Yelloww                                   |
      | team_member_account_email      | behat-pa1-admin@publicguardian.gsi.gov.uk |
      | team_member_account_roleName_0 | ROLE_PA_ADMIN                             |
    And I press "team_member_account_save"
    Then the form should be valid
    Then I should see the "team-user-behat-pa1-adminpublicguardiangsigovuk" region

  Scenario: activate PA_ADMIN user
    Given emails are sent from "deputy" area
    And I go to "/logout"
    And I open the "/user/activate/" link from the email
    # password step
    When I fill in the following:
      | set_password_password_first  | Abcd1234 |
      | set_password_password_second | Abcd1234 |
    When I click on "save"
    Then the form should be valid
    # assert pre-fill
    Then the following fields should have the corresponding values:
      | user_details_firstname | Markk Admin |
      | user_details_lastname  | Yelloww     |
    # add change details
    When I fill in the following:
      | user_details_firstname | Mark Admin          |
      | user_details_lastname  | Yellow              |
      | user_details_jobTitle  | Solicitor assistant |
      | user_details_phoneMain | 203457234582435     |
    And I press "user_details_save"
    Then the form should be valid
    # check I'm in the dashboard and I see the same clients
    And I should see the "client-1000010" region
    # check I see all the users
    When I click on "pa-settings, user-accounts"
    Then I should see the "team-user-behat-pa1publicguardiangsigovuk" region
    And I should see the "team-user-behat-pa1-adminpublicguardiangsigovuk" region

  Scenario: PA_ADMIN logs in and adds PA_TEAM_MEMBER
    Given I am logged in as "behat-pa1-admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-settings, user-accounts, add"
    # add user ADMIN
    When I fill in the following:
      | team_member_account_firstname  | Robertt Team member                             |
      | team_member_account_lastname   | Blackk                                          |
      | team_member_account_email      | behat-pa1-team-member@publicguardian.gsi.gov.uk |
      | team_member_account_roleName_1 | ROLE_PA_TEAM_MEMBER                             |
    And I press "team_member_account_save"
    Then the form should be valid
    # check all 3 users are displayed
    Then I should see the "team-user-behat-pa1publicguardiangsigovuk" region
    Then I should see the "team-user-behat-pa1-adminpublicguardiangsigovuk" region
    Then I should see the "team-user-behat-pa1-team-memberpublicguardiangsigovuk" region

  Scenario: activate ROLE_PA_TEAM_MEMBER user
    Given emails are sent from "deputy" area
    And I go to "/logout"
    And I open the "/user/activate/" link from the email
    # password step
    When I fill in the following:
      | set_password_password_first  | Abcd1234 |
      | set_password_password_second | Abcd1234 |
    When I click on "save"
    Then the form should be valid
    # assert pre-fill
    Then the following fields should have the corresponding values:
      | user_details_firstname | Robertt Team member |
      | user_details_lastname  | Blackk              |
    # add change details
    When I fill in the following:
      | user_details_firstname | Robert Team member |
      | user_details_lastname  | Black              |
      | user_details_jobTitle  | Solicitor helper       |
      | user_details_phoneMain | 45687546322543    |
    And I press "user_details_save"
    Then the form should be valid
    # check I'm in the dashboard and I see the same clients
    And I should see the "client-1000010" region
    # check I see all the users
    When I click on "pa-settings, user-accounts"
    Then I should see the "team-user-behat-pa1publicguardiangsigovuk" region
    Then I should see the "team-user-behat-pa1-adminpublicguardiangsigovuk" region
    Then I should see the "team-user-behat-pa1-team-memberpublicguardiangsigovuk" region
    #And I should not see the "add" link