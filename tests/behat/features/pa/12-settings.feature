Feature: PA settings

  Scenario: names PA logs in and views profile page
    Given I load the application status from "team-users-complete"
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-settings"

    # settings page
    Then I should see the "user-accounts" link
    And I should see the "profile-show" link
    When I click on "profile-show"

    # profile page
    Then I should see "John Named Green" in the "profile-name" region
    And I should see "behat-pa1@publicguardian.gsi.gov.uk" in the "profile-email" region
    And I should see "Solicitor" in the "profile-job" region
    And I should see "10000000001" in the "profile-phone" region

  Scenario: named PA logs in and updates profile and does not see removeAdmin field
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-settings, profile-show, pa-edit"
    Then I should not see "Give up administrator rights"
    Then I fill in the following:
      | profile_firstname  | John Named Chap                       |
      | profile_lastname   | Greenish                              |
      | profile_email      | behat-pa1@publicguardian.gsi.gov.uk   |
      | profile_jobTitle   | Solicitor General                     |
      | profile_phoneMain  | 10000000011                           |
    And I press "profile_save"
    Then the form should be valid
    Then I should see "John Named Chap Greenish" in the "profile-name" region
    And I should see "behat-pa1@publicguardian.gsi.gov.uk" in the "profile-email" region
    And I should see "Solicitor General" in the "profile-job" region
    And I should see "10000000011" in the "profile-phone" region

  Scenario: PA Admin logs in and updates profile and sees removeAdmin field but does not
    Given I am logged in as "behat-pa1-admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-settings, profile-show, pa-edit"
    Then I should see "Give up administrator rights"
    Then I fill in the following:
      | profile_firstname  | Mark Admin Chap                           |
      | profile_lastname   | Yellowish                                 |
      | profile_email      | behat-pa1-admin@publicguardian.gsi.gov.uk |
      | profile_jobTitle   | Solicitor Assistant                       |
      | profile_phoneMain  | 10000000012                               |
    And I press "profile_save"
    Then the form should be valid
    Then I should see "Mark Admin Chap Yellowish" in the "profile-name" region
    And I should see "behat-pa1-admin@publicguardian.gsi.gov.uk" in the "profile-email" region
    And I should see "Solicitor Assistant" in the "profile-job" region
    And I should see "10000000012" in the "profile-phone" region

  Scenario: PA Admin logs in and updates profile and removes admin
    Given I am logged in as "behat-pa1-admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-settings, profile-show, pa-edit"
    Then I should see "Give up administrator rights"
    When I check "profile_removeAdmin_0"
    And I press "profile_save"
    Then the form should be valid
    And I should be on "/login"

  Scenario: PA Admin is no longer admin and tests nav
    Given I am logged in as "behat-pa1-admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-settings, user-accounts"
    Then I should not see "Edit" in the "team-user-behat-pa1-adminpublicguardiangsigovuk" region
    And I should not see "Edit" in the "team-user-behat-pa1-team-memberpublicguardiangsigovuk" region
    But I should not see "Edit" in the "team-user-behat-pa1publicguardiangsigovuk" region

  Scenario: PA Team member logs in and edits info
    Given I am logged in as "behat-pa3-team-member@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-settings, profile-show, pa-edit"
    Then I should not see "Give up administrator rights"
    When I fill in the following:
      | profile_firstname  | Mark Team Member                                |
      | profile_lastname   |                                                 |
      | profile_email      | behat-pa3-team-member@publicguardian.gsi.gov.uk |
      | profile_jobTitle   | Solicitor helper                                |
      | profile_phoneMain  | 30000000003                                     |
    And I press "profile_save"
    Then the following fields should have an error:
      | profile_lastname   |
    When I fill in the following:
      | profile_firstname  | Tim Team Member                                |
      | profile_lastname   | Chap                                            |
      | profile_email      | behat-pa3-team-member@publicguardian.gsi.gov.uk |
      | profile_jobTitle   | Solicitor helper                                |
      | profile_phoneMain  | 30000000123                                     |
    And I press "profile_save"
    Then the form should be valid
    Then I should see "Tim Team Member Chap" in the "profile-name" region
    And I should see "behat-pa3-team-member@publicguardian.gsi.gov.uk" in the "profile-email" region
    And I should see "Solicitor helper" in the "profile-job" region
    And I should see "30000000123" in the "profile-phone" region