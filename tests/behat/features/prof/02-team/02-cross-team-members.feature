Feature: PROF cross team members

  Scenario: PROF_ADMIN3 logs in and adds PROF_TEAM_MEMBER using email address belonging to other team
    Given I load the application status from "prof-team-users-complete"
    And I am logged in as "behat-prof3@publicguardian.gsi.gov.uk" with password "Abcd1234"
#    When I click on "org-settings, user-accounts, add"
#    # add user team member
#    And I fill in the following:
#      | team_member_account_firstname  | PROF3                                             |
#      | team_member_account_lastname   | Team Member                                     |
#      | team_member_account_email      | behat-prof3-team-member@publicguardian.gsi.gov.uk |
#      | team_member_account_roleName_1 | ROLE_PROF_TEAM_MEMBER                             |
#    And I press "team_member_account_save"
#    Then die