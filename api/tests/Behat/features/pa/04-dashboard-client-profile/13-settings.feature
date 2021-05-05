Feature: PA settings

  Scenario: named PA logs in and views profile page
    And I am logged in as "behat-pa1@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I click on "org-settings"
    # settings page
    Then I should see the "org-accounts" link
    And I should see the "profile-show" link
    And I should see the "password-edit" link
    When I click on "profile-show"

    # profile page
    Then I should see "John Named Green" in the "profile-name" region
    And I should see "behat-pa1@publicguardian.gov.uk" in the "profile-email" region
    And I should see "Solicitor" in the "profile-job" region
    And I should see "10000000001" in the "profile-phone" region

  Scenario: named PA logs in and updates profile and does not see removeAdmin field
    Given I am logged in as "behat-pa1@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I click on "org-settings, profile-show, profile-edit"
    Then I should not see "Give up administrator rights"
    Then I fill in the following:
      | profile_firstname  | John Named Chap                       |
      | profile_lastname   | Greenish                              |
      | profile_jobTitle   | Solicitor General                     |
      | profile_phoneMain  | 10000000011                           |
      | profile_address1         | 123 Streetname |
      | profile_addressPostcode  | AB1 2CD        |
      | profile_addressCountry   | GB             |
    And I press "profile_save"
    Then the form should be valid
    Then I should see "John Named Chap Greenish" in the "profile-name" region
    And I should see "behat-pa1@publicguardian.gov.uk" in the "profile-email" region
    And I should see "Solicitor General" in the "profile-job" region
    And I should see "10000000011" in the "profile-phone" region
    And I should see "123 Streetname" in the "profile-address" region
    And I should see "AB1 2CD" in the "profile-address" region
    And I should see "United Kingdom" in the "profile-address" region

  Scenario: PA Admin logs in and updates profile and sees removeAdmin field but does not
    Given I am logged in as "JamesShawAdmin@behat-test.com" with password "DigidepsPass1234"
    When I click on "org-settings, profile-show, profile-edit"
    Then I should see "Give up administrator rights"
    Then I fill in the following:
      | profile_firstname  | Mark Admin Chap                           |
      | profile_lastname   | Yellowish                                 |
      | profile_jobTitle   | Solicitor Assistant                       |
      | profile_phoneMain  | 10000000012                               |
    And I press "profile_save"
    Then the form should be valid
    Then I should see "Mark Admin Chap Yellowish" in the "profile-name" region
    And I should see "JamesShawAdmin@behat-test.com" in the "profile-email" region
    And I should see "Solicitor Assistant" in the "profile-job" region
    And I should see "10000000012" in the "profile-phone" region

  Scenario: PA Admin logs in and updates profile and removes admin
    Given I am logged in as "JamesShawAdmin@behat-test.com" with password "DigidepsPass1234"
    When I click on "org-settings, profile-show, profile-edit"
    Then I should see "Give up administrator rights"
    When I check "Give up administrator rights"
    And I press "profile_save"
    Then the form should be valid
    And I should be on "/login"

  Scenario: PA Admin is no longer admin and tests nav
    Given I am logged in as "JamesShawAdmin@behat-test.com" with password "DigidepsPass1234"
    When I click on "org-settings, org-accounts"
    And I follow "PA OPG"
    Then I should not see "Edit" in the "team-user-behat-pa1publicguardiangovuk" region
    And I should not see "Edit" in the "team-user-emilyhainesnamedbehat-testcom" region
    But I should not see "Edit" in the "team-user-kimpetrasteammemberbehat-testcom" region

  Scenario: PA Team member logs in and edits info
    Given I am logged in as "KimPetrasTeamMember@behat-test.com" with password "DigidepsPass1234"
    When I click on "org-settings, profile-show, profile-edit"
    Then I should not see "Give up administrator rights"
    When I fill in the following:
      | profile_firstname  |                                                 |
      | profile_lastname   |                                                 |
      | profile_jobTitle   |                                                 |
      | profile_phoneMain  |                                                 |
    And I press "profile_save"
    Then the following fields should have an error:
      | profile_firstname        |
      | profile_lastname         |
    When I fill in the following:
      | profile_firstname        | Tim Team Member                                 |
      | profile_lastname         | Chap                                            |
      | profile_jobTitle         | Solicitor helper                                |
      | profile_phoneMain        | 30000000123                                     |
      | profile_address1         | 123 SomeRoad                                    |
      | profile_addressPostcode  | AA1 2BB                                         |
      | profile_addressCountry   | GB                                              |
    And I press "profile_save"
    Then the form should be valid
    Then I should see "Tim Team Member Chap" in the "profile-name" region
    And I should see "KimPetrasTeamMember@behat-test.com" in the "profile-email" region
    And I should see "Solicitor helper" in the "profile-job" region
    And I should see "30000000123" in the "profile-phone" region
    And I should see "123 SomeRoad" in the "profile-address" region
    And I should see "AA1 2BB" in the "profile-address" region
    And I should see "United Kingdom" in the "profile-address" region

  Scenario: Named PA logs in and changes password
    Given I am logged in as "behat-pa1@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I click on "org-settings, password-edit"
    Then I should see "Change password"
    When I press "change_password_save"
    Then the following fields should have an error:
      | change_password_current_password     |
      | change_password_password_first |
    #incorrect password
    When I fill in the following:
      | change_password_current_password       | Moo       |
      | change_password_password_first   | DigidepsPass12345  |
      | change_password_password_second  | DigidepsPass12345  |
    When I press "change_password_save"
    Then the following fields should have an error:
      | change_password_current_password     |
    #correct password, unmatching new passwords
    When I fill in the following:
      | change_password_current_password       | DigidepsPass1234  |
      | change_password_password_first   | DigidepsPass1111  |
      | change_password_password_second  | DigidepsPass2222  |
    When I press "change_password_save"
    Then the following fields should have an error:
      | change_password_password_first   |
    #not long enough
    When I fill in the following:
      | change_password_current_password       | DigidepsPass1234 |
      | change_password_password_first   | Digideps1234  |
      | change_password_password_second  | Digideps1234  |
    When I press "change_password_save"
    Then the following fields should have an error:
      | change_password_password_first   |
    #no uppercase
    When I fill in the following:
      | change_password_current_password       | DigidepsPass1234  |
      | change_password_password_first   | digideps1234  |
      | change_password_password_second  | digideps1234  |
    When I press "change_password_save"
    Then the following fields should have an error:
      | change_password_password_first   |
    #no numbers
    When I fill in the following:
      | change_password_current_password       | DigidepsPass1234  |
      | change_password_password_first   | DigidepsPassword  |
      | change_password_password_second  | DigidepsPassword  |
    When I press "change_password_save"
    Then the following fields should have an error:
      | change_password_password_first   |
    #no lowercase
    When I fill in the following:
      | change_password_current_password       | DigidepsPass1234  |
      | change_password_password_first   | DIGIDEPSPASS1234  |
      | change_password_password_second  | DIGIDEPSPASS1234  |
    When I press "change_password_save"
    Then the following fields should have an error:
      | change_password_password_first   |
    # too common password
    When I fill in the following:
      | change_password_current_password       | DigidepsPass1234  |
      | change_password_password_first   | Password123  |
      | change_password_password_second  | Password123  |
    When I press "change_password_save"
    Then the following fields should have an error:
      | change_password_password_first   |
    # Finally a valid one
    When I fill in the following:
      | change_password_current_password       | DigidepsPass1234  |
      | change_password_password_first   | DigidepsPass2345  |
      | change_password_password_second  | DigidepsPass2345  |
    When I press "change_password_save"
    Then the form should be valid
    And the url should match "/login"
    And I should see "Sign in with your new password"
    Given I am logged in as "behat-pa1@publicguardian.gov.uk" with password "DigidepsPass2345"
    When I click on "org-settings, password-edit"
    When I fill in the following:
      | change_password_current_password       | DigidepsPass2345   |
      | change_password_password_first   | DigidepsPass1234!! |
      | change_password_password_second  | DigidepsPass1234!! |
    When I press "change_password_save"
    Then the form should be valid
    And the url should match "/login"
