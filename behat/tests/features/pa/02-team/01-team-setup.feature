#Feature: PA team setup
#
#  Background:
#    Given the organisation "publicguardian.gov.uk" is active
#    And "behat-pa1@publicguardian.gov.uk" has been added to the "publicguardian.gov.uk" organisation
#
#  Scenario: team page
#    Given I load the application status from "pa-users-uploaded"
#    And I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
#    When I click on "org-settings"
#    # settings page
#    And I click on "user-accounts"
#    Then I should see the "team-user-behat-pa1publicguardiangovuk" region
#
#  Scenario: named PA logs in and adds PA_ADMIN user
#    Given I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
#    When I click on "org-settings, user-accounts"
#    # add user - test form
#    When I click on "add"
#    And I press "organisation_member_save"
#    Then the following fields should have an error:
#      | organisation_member_firstname  |
#      | organisation_member_lastname   |
#      | organisation_member_email      |
#      | organisation_member_roleName_0 |
#      | organisation_member_roleName_1 |
#    # add user ADMIN invalid domain
#    When I fill in the following:
#      | organisation_member_firstname  | Markk Admin                               |
#      | organisation_member_lastname   | Yelloww                                   |
#      | organisation_member_email      | behat-pa1-admin@someotherdomain.gov.uk |
#      | organisation_member_roleName_0 | ROLE_PA_ADMIN                             |
#    And I press "organisation_member_save"
#    Then the following fields should have an error:
#      | organisation_member_email      |
#    # add valid user details
#    When I fill in the following:
#      | organisation_member_firstname  | Markk Admin                               |
#      | organisation_member_lastname   | Yelloww                                   |
#      | organisation_member_email      | behat-pa1-admin@publicguardian.gov.uk |
#      | organisation_member_roleName_0 | ROLE_PA_ADMIN                             |
#    And I press "organisation_member_save"
#    Then the form should be valid
#    Then I should see the "team-user-behat-pa1-adminpublicguardiangovuk" region
#
#  Scenario: activate PA_ADMIN user
#    Given emails are sent from "deputy" area
#    And I activate the user with password "Abcd1234"
#    # assert pre-fill
#    Then the following fields should have the corresponding values:
#      | user_details_firstname | Markk Admin |
#      | user_details_lastname  | Yelloww     |
#    # add change details
#    When I fill in the following:
#      | user_details_firstname | Mark Admin          |
#      | user_details_lastname  | Yellow              |
#      | user_details_jobTitle  | Solicitor assistant |
#      | user_details_phoneMain | 10000000002         |
#    And I press "user_details_save"
#    Then the form should be valid
#    # check I'm in the dashboard and I see the same clients
#    And the URL should match "/org"
#    And I should see the "client-01000010" region
#    # check I see all the users
#    When I click on "org-settings, user-accounts"
#    Then I should see the "team-user-behat-pa1publicguardiangovuk" region
#    And I should see the "team-user-behat-pa1-adminpublicguardiangovuk" region
#
#  Scenario: PA_ADMIN logs in and adds PA_TEAM_MEMBER with invalid email
#    Given I am logged in as "behat-pa1-admin@publicguardian.gov.uk" with password "Abcd1234"
#    When I click on "org-settings, user-accounts, add"
#    # add user ADMIN
#    When I fill in the following:
#      | organisation_member_firstname  | Robertt Team member                             |
#      | organisation_member_lastname   | Blackk                                          |
#      | organisation_member_email      | behat-pa1-team-member@@publicguardian.gov.uk |
#      | organisation_member_roleName_1 | ROLE_PA_TEAM_MEMBER                             |
#    And I press "organisation_member_save"
#    Then the following fields should have an error:
#      | organisation_member_email      |
#
#  Scenario: PA_ADMIN logs in and adds PA_TEAM_MEMBER
#    Given I am logged in as "behat-pa1-admin@publicguardian.gov.uk" with password "Abcd1234"
#    When I click on "org-settings, user-accounts, add"
#    # add user ADMIN
#    When I fill in the following:
#      | organisation_member_firstname  | Robertt Team member                             |
#      | organisation_member_lastname   | Blackk                                          |
#      | organisation_member_email      | behat-pa1-team-member@publicguardian.gov.uk |
#      | organisation_member_roleName_1 | ROLE_PA_TEAM_MEMBER                             |
#    And I press "organisation_member_save"
#    Then the form should be valid
#    # check all 3 users are displayed
#    Then I should see the "team-user-behat-pa1publicguardiangovuk" region
#    Then I should see the "team-user-behat-pa1-adminpublicguardiangovuk" region
#    Then I should see the "team-user-behat-pa1-team-memberpublicguardiangovuk" region
#
#  Scenario: activate ROLE_PA_TEAM_MEMBER user
#    Given emails are sent from "deputy" area
#    And I activate the user with password "Abcd1234"
#    # assert pre-fill
#    Then the following fields should have the corresponding values:
#      | user_details_firstname | Robertt Team member |
#      | user_details_lastname  | Blackk              |
#    # add change details
#    When I fill in the following:
#      | user_details_firstname | Robert Team member |
#      | user_details_lastname  | Black              |
#      | user_details_jobTitle  | Solicitor helper   |
#      | user_details_phoneMain | 10000000003        |
#    And I press "user_details_save"
#    Then the form should be valid
#    # check I'm in the dashboard and I see the same clients
#    And I should see the "client-01000010" region
#    # check I see all the users
#    When I click on "org-settings, user-accounts"
#    Then I should see the "team-user-behat-pa1publicguardiangovuk" region
#    Then I should see the "team-user-behat-pa1-adminpublicguardiangovuk" region
#    Then I should see the "team-user-behat-pa1-team-memberpublicguardiangovuk" region
#    And I should not see the "add" link
#
#  Scenario: PA (named) logs in and edit users
#    Given I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
#    When I click on "org-settings, user-accounts"
#    # edit PA named
#    And I click on "edit" in the "team-user-behat-pa1-team-memberpublicguardiangovuk" region
#    Then the following fields should have the corresponding values:
#      | organisation_member_firstname | Robert Team member                              |
#      | organisation_member_lastname  | Black                                           |
#      | organisation_member_email     | behat-pa1-team-member@publicguardian.gov.uk |
#      | organisation_member_jobTitle  | Solicitor helper                                |
#      | organisation_member_phoneMain | 10000000003                                     |
#    And I should not see a "organisation_member_roleName_0" element
#    And I should not see a "organisation_member_roleName_1" element
#    When I fill in the following:
#      | organisation_member_firstname | Bobby Team member                               |
#      | organisation_member_lastname  | BlackAndBlue                                    |
#      | organisation_member_email     | behat-pa1-team-member@publicguardian.gov.uk |
#      | organisation_member_jobTitle  | Helper solicitor                                |
#      | organisation_member_phoneMain | +4410000000003                                  |
#    And I press "organisation_member_save"
#    Then the form should be valid
#    And I should see "Bobby Team member" in the "team-user-behat-pa1-team-memberpublicguardiangovuk" region
#    And I should see "BlackAndBlue" in the "team-user-behat-pa1-team-memberpublicguardiangovuk" region
#    And I should see "Helper solicitor" in the "team-user-behat-pa1-team-memberpublicguardiangovuk" region
#    And I should see "+4410000000003" in the "team-user-behat-pa1-team-memberpublicguardiangovuk" region
#    # PA named edits Admin, downgrade role into team member
#    Given I save the application status into "pa-team-before-downgrading-admin"
#    And I should see "Administrator" in the "team-user-behat-pa1-adminpublicguardiangovuk" region
#    When I click on "edit" in the "team-user-behat-pa1-adminpublicguardiangovuk" region
#    Then the "organisation_member_roleName_0" field should contain "ROLE_PA_ADMIN"
#    When I fill in "organisation_member_roleName_1" with "ROLE_PA_TEAM_MEMBER"
#    And I press "organisation_member_save"
#    Then I should not see "Administrator" in the "team-user-behat-pa1-adminpublicguardiangovuk" region
#    # restore Admin role
#    And I load the application status from "pa-team-before-downgrading-admin"
#    # check PA named can edit team members
#    When I click on "edit" in the "team-user-behat-pa1-team-memberpublicguardiangovuk" region
#    Then the "organisation_member_roleName_1" field should contain "ROLE_PA_TEAM_MEMBER"
#    Then the response status code should be 200
#
#  Scenario: PA admin logs in and edit users
#    Given I am logged in as "behat-pa1-admin@publicguardian.gov.uk" with password "Abcd1234"
#    When I click on "org-settings, user-accounts"
#    And I should see "Edit" in the "team-user-behat-pa1-team-memberpublicguardiangovuk" region
#    But I should not see "Edit" in the "team-user-behat-pa1publicguardiangovuk" region
#
#  Scenario: PA (named) deputy adds, then removes a PA_ADMIN user
#    Given I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
#    When I click on "org-settings, user-accounts"
#    Then I should not see "Remove" in the "team-user-behat-pa1publicguardiangovuk" region
#    When I click on "add"
#    Then the response status code should be 200
#    And I press "organisation_member_save"
#    When I fill in the following:
#      | organisation_member_firstname  | Adam Admin                               |
#      | organisation_member_lastname   | Cyan                                   |
#      | organisation_member_email      | behat-pa1-admin2@publicguardian.gov.uk |
#      | organisation_member_roleName_0 | ROLE_PA_ADMIN                             |
#    And I press "organisation_member_save"
#    Then the form should be valid
#    Then the response status code should be 200
#    Then I should see the "team-user-behat-pa1-admin2publicguardiangovuk" region
#    Then I should see "Remove" in the "team-user-behat-pa1-admin2publicguardiangovuk" region
#    But I should not see "Remove" in the "team-user-behat-pa1publicguardiangovuk" region
#    Then I click on "delete" in the "team-user-behat-pa1-admin2publicguardiangovuk" region
#    Then the response status code should be 200
#    # test cancel button on confirmation page
#    When I click on "confirm-cancel"
#    Then the response status code should be 200
#    Then I click on "delete" in the "team-user-behat-pa1-admin2publicguardiangovuk" region
#    Then the response status code should be 200
#    # now confirm
#    When I click on "confirm"
#    Then the response status code should be 200
#    Then I should not see the "team-user-behat-pa1-admin2publicguardiangovuk" region
#
#  Scenario: PA_ADMIN logs in, adds then removes a PA_TEAM_MEMBER
#    Given I am logged in as "behat-pa1-admin@publicguardian.gov.uk" with password "Abcd1234"
#    When I click on "org-settings, user-accounts, add"
#    # add user team member
#    When I fill in the following:
#      | organisation_member_firstname  | Andy Team member                             |
#      | organisation_member_lastname   | Team Member                                          |
#      | organisation_member_email      | behat-pa1-team-member2@publicguardian.gov.uk |
#      | organisation_member_roleName_1 | ROLE_PA_TEAM_MEMBER                             |
#    And I press "organisation_member_save"
#    Then the form should be valid
#    # check all 3 users are displayed
#    Then I should see the "team-user-behat-pa1publicguardiangovuk" region
#    Then I should see the "team-user-behat-pa1-adminpublicguardiangovuk" region
#    Then I should see the "team-user-behat-pa1-team-member2publicguardiangovuk" region
#    Then I should see "Remove" in the "team-user-behat-pa1-team-member2publicguardiangovuk" region
#    But I should not see "Remove" in the "team-user-behat-pa1-adminpublicguardiangovuk" region
#    Then I click on "delete" in the "team-user-behat-pa1-team-member2publicguardiangovuk" region
#    Then the response status code should be 200
#    When I click on "confirm"
#    Then the response status code should be 200
#    Then I should not see the "team-user-behat-pa1-team-member2publicguardiangovuk" region
#
#  Scenario: named PA3 logs in, adds and activates PA_ADMIN user
#    Given I am logged in as "behat-pa3@publicguardian.gov.uk" with password "Abcd1234"
#    When I click on "org-settings, user-accounts"
#    And I click on "add"
#    And I fill in the following:
#      | organisation_member_firstname  | PA3                                       |
#      | organisation_member_lastname   | Admin                                     |
#      | organisation_member_email      | behat-pa3-admin@publicguardian.gov.uk |
#      | organisation_member_roleName_0 | ROLE_PA_ADMIN                             |
#    And I press "organisation_member_save"
#    Then the form should be valid
#    Then I should see the "team-user-behat-pa3-adminpublicguardiangovuk" region
#    When emails are sent from "deputy" area
#    And I activate the user with password "Abcd1234"
#    When I fill in the following:
#      | user_details_jobTitle  | Solicitor assistant |
#      | user_details_phoneMain | 20000000002         |
#    And I press "user_details_save"
#    Then the form should be valid
#    And I should see the "client-03000001" region
#
#  Scenario: PA_ADMIN3 logs in, adds and activates PA_TEAM_MEMBER
#    Given I am logged in as "behat-pa3-admin@publicguardian.gov.uk" with password "Abcd1234"
#    When I click on "org-settings, user-accounts, add"
#    When I fill in the following:
#      | organisation_member_firstname  | PA3                                             |
#      | organisation_member_lastname   | Team Member                                     |
#      | organisation_member_email      | behat-pa3-team-member@publicguardian.gov.uk |
#      | organisation_member_roleName_1 | ROLE_PA_TEAM_MEMBER                             |
#    And I press "organisation_member_save"
#    Then the form should be valid
#    When emails are sent from "deputy" area
#    And I activate the user with password "Abcd1234"
#    When I fill in the following:
#      | user_details_jobTitle  | Solicitor helper   |
#      | user_details_phoneMain | 30000000003        |
#    And I press "user_details_save"
#    Then the form should be valid
#    And I save the application status into "team-users-complete"
#    And I should see the "client-03000001" region
#
#  Scenario: PA_ADMIN3 logs in and edits PA_TEAM_MEMBER using existing email address
#    Given I am logged in as "behat-pa3@publicguardian.gov.uk" with password "Abcd1234"
#    When I click on "org-settings, user-accounts"
#    # edit PA named
#    When I click on "edit" in the "team-user-behat-pa3-team-memberpublicguardiangovuk" region
#    And I fill in the following:
#      | organisation_member_firstname | Edited PA3                                |
#      | organisation_member_lastname  | Edited Team Member                        |
#      | organisation_member_email     | behat-pa3-admin@publicguardian.gov.uk |
#    And I press "organisation_member_save"
#    Then the following fields should have an error:
#      | organisation_member_email |
#
#  Scenario: PA_ADMIN3 logs in and edits PA_TEAM_MEMBER using existing email address for a deleted user
#    Given I am logged in as "behat-pa3@publicguardian.gov.uk" with password "Abcd1234"
#    When I click on "org-settings, user-accounts"
#    # delete existing admin user
#    And I click on "delete" in the "team-user-behat-pa3-adminpublicguardiangovuk" region
#    And I click on "confirm"
#    # edit PA named
#    When I click on "edit" in the "team-user-behat-pa3-team-memberpublicguardiangovuk" region
#    And I fill in the following:
#      | organisation_member_firstname | Edited PA3                                |
#      | organisation_member_lastname  | Edited Team Member                        |
#      | organisation_member_email     | behat-pa3-admin@publicguardian.gov.uk |
#    And I press "organisation_member_save"
#    Then the form should be valid
#    And I should see "behat-pa3-admin@publicguardian.gov.uk" in the "team-user-behat-pa3-adminpublicguardiangovuk" region
#
#  Scenario: PA_ADMIN3 logs in and adds PA_TEAM_MEMBER using existing email address
#    Given I load the application status from "team-users-complete"
#    And I am logged in as "behat-pa3@publicguardian.gov.uk" with password "Abcd1234"
#    When I click on "org-settings, user-accounts, add"
#    # add user team member
#    And I fill in the following:
#      | organisation_member_firstname  | PA3                                             |
#      | organisation_member_lastname   | Team Member                                     |
#      | organisation_member_email      | behat-pa3-team-member@publicguardian.gov.uk |
#      | organisation_member_roleName_1 | ROLE_PA_TEAM_MEMBER                             |
#    And I press "organisation_member_save"
#    Then the following fields should have an error:
#      | organisation_member_email |
#
#  Scenario: PA_ADMIN3 logs in and adds PA_TEAM_MEMBER using existing email address for a deleted user
#    Given I am logged in as "behat-pa3@publicguardian.gov.uk" with password "Abcd1234"
#    When I click on "org-settings, user-accounts"
#    # delete existing admin user
#    And I click on "delete" in the "team-user-behat-pa3-team-memberpublicguardiangovuk" region
#    And I click on "confirm"
#    When I click on "org-settings, user-accounts, add"
#    # add user team member
#    And I fill in the following:
#      | organisation_member_firstname  | PA3                                             |
#      | organisation_member_lastname   | Team Member                                     |
#      | organisation_member_email      | behat-pa3-team-member@publicguardian.gov.uk |
#      | organisation_member_roleName_1 | ROLE_PA_TEAM_MEMBER                             |
#    And I press "organisation_member_save"
#    Then the form should be valid
#    And I should see "behat-pa3-team-member@publicguardian.gov.uk" in the "team-user-behat-pa3-team-memberpublicguardiangovuk" region
#    And I load the application status from "team-users-complete"
