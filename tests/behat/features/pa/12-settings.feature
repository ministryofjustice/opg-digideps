Feature: PA settings

  Scenario: named PA logs in and views profile page
    Given I load the application status from "team-users-complete"
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-settings"

    # settings page
    Then I should see the "user-accounts" link
    And I should see the "profile-show" link
    And I should see the "password-edit" link
    When I click on "profile-show"

    # profile page
    Then I should see "John Named Green" in the "profile-name" region
    And I should see "behat-pa1@publicguardian.gsi.gov.uk" in the "profile-email" region
    And I should see "Solicitor" in the "profile-job" region
    And I should see "10000000001" in the "profile-phone" region

  Scenario: named PA logs in and updates profile and does not see removeAdmin field
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-settings, profile-show, profile-edit"
    Then I should not see "Give up administrator rights"
    Then I fill in the following:
      | profile_firstname  | John Named Chap                       |
      | profile_lastname   | Greenish                              |
      | profile_email      | behat-pa1@publicguardian.gsi.gov.uk   |
      | profile_jobTitle   | Solicitor General                     |
      | profile_phoneMain  | 10000000011                           |
    And I press "profile_save"
    Then the following fields should have an error:
      | profile_address1         |
      | profile_addressPostcode  |
      | profile_addressCountry   |
    When I fill in the following:
      | profile_address1         | 123 Streetname |
      | profile_addressPostcode  | AB1 2CD        |
      | profile_addressCountry   | GB             |
    And I press "profile_save"
    Then the form should be valid
    Then I should see "John Named Chap Greenish" in the "profile-name" region
    And I should see "behat-pa1@publicguardian.gsi.gov.uk" in the "profile-email" region
    And I should see "Solicitor General" in the "profile-job" region
    And I should see "10000000011" in the "profile-phone" region
    And I should see "123 Streetname" in the "profile-address" region
    And I should see "AB1 2CD" in the "profile-address" region
    And I should see "United Kingdom" in the "profile-address" region

  Scenario: PA Admin logs in and updates profile and sees removeAdmin field but does not
    Given I am logged in as "behat-pa1-admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-settings, profile-show, profile-edit"
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
    When I click on "pa-settings, profile-show, profile-edit"
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
    When I click on "pa-settings, profile-show, profile-edit"
    Then I should not see "Give up administrator rights"
    When I fill in the following:
      | profile_firstname  |                                                 |
      | profile_lastname   |                                                 |
      | profile_email      |                                                 |
      | profile_jobTitle   |                                                 |
      | profile_phoneMain  |                                                 |
    And I press "profile_save"
    Then the following fields should have an error:
      | profile_firstname        |
      | profile_lastname         |
      | profile_email            |
      | profile_jobTitle         |
      | profile_phoneMain        |
      | profile_address1         |
      | profile_addressPostcode  |
      | profile_addressCountry   |
    When I fill in the following:
      | profile_firstname        | Tim Team Member                                 |
      | profile_lastname         | Chap                                            |
      | profile_email            | behat-pa3-team-member@publicguardian.gsi.gov.uk |
      | profile_jobTitle         | Solicitor helper                                |
      | profile_phoneMain        | 30000000123                                     |
      | profile_address1         | 123 SomeRoad                                    |
      | profile_addressPostcode  | AA1 2BB                                         |
      | profile_addressCountry   | GB                                              |
    And I press "profile_save"
    Then the form should be valid
    Then I should see "Tim Team Member Chap" in the "profile-name" region
    And I should see "behat-pa3-team-member@publicguardian.gsi.gov.uk" in the "profile-email" region
    And I should see "Solicitor helper" in the "profile-job" region
    And I should see "30000000123" in the "profile-phone" region
    And I should see "123 SomeRoad" in the "profile-address" region
    And I should see "AA1 2BB" in the "profile-address" region
    And I should see "United Kingdom" in the "profile-address" region

  Scenario: Named PA logs in and changes password
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I click on "pa-settings, password-edit"
    Then I should see "Change password"
    When I press "change_password_save"
    Then the following fields should have an error:
      | change_password_current_password     |
      | change_password_plain_password_first |
    #incorrect password
    When I fill in the following:
      | change_password_current_password       | Moo       |
      | change_password_plain_password_first   | Abcd2345  |
      | change_password_plain_password_second  | Abcd2345  |
    When I press "change_password_save"
    Then the following fields should have an error:
      | change_password_current_password     |
    #correct password, unmatching new passwords
    When I fill in the following:
      | change_password_current_password       | Abcd1234  |
      | change_password_plain_password_first   | Abcd1111  |
      | change_password_plain_password_second  | Abcd2222  |
    When I press "change_password_save"
    Then the following fields should have an error:
      | change_password_plain_password_first   |
    #various password inadequacies
    When I fill in the following:
      | change_password_current_password       | Abcd1234 |
      | change_password_plain_password_first   | Abcd123  |
      | change_password_plain_password_second  | Abcd123  |
    When I press "change_password_save"
    Then the following fields should have an error:
      | change_password_plain_password_first   |
    When I fill in the following:
      | change_password_current_password       | Abcd1234  |
      | change_password_plain_password_first   | abcd1234  |
      | change_password_plain_password_second  | abcd1234  |
    When I press "change_password_save"
    Then the following fields should have an error:
      | change_password_plain_password_first   |
    When I fill in the following:
      | change_password_current_password       | Abcd1234  |
      | change_password_plain_password_first   | Abcdefgh  |
      | change_password_plain_password_second  | Abcdefgh  |
    When I press "change_password_save"
    Then the following fields should have an error:
      | change_password_plain_password_first   |
    When I fill in the following:
      | change_password_current_password       | Abcd1234  |
      | change_password_plain_password_first   | ABCD1234  |
      | change_password_plain_password_second  | ABCD1234  |
    When I press "change_password_save"
    Then the following fields should have an error:
      | change_password_plain_password_first   |
    # Finally a valid one
    When I fill in the following:
      | change_password_current_password       | Abcd1234  |
      | change_password_plain_password_first   | Abcd2345  |
      | change_password_plain_password_second  | Abcd2345  |
    When I press "change_password_save"
    Then the form should be valid
    And I should see "Password edited"
    And I should be on "/pa/settings"