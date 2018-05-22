Feature: Add PROF users and activate PROF user (journey)

  Scenario: add PROF users
    Given I load the application status from "init-prof"
    And emails are sent from "admin" area
    And I reset the email log
    And I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
      # upload PROF users
    When I click on "admin-upload-pa"
    When I attach the file "behat-prof.csv" to "admin_upload_file"
    And I press "admin_upload_upload"
    Then the form should be valid
    #Then I should see "Added 1 PROF users"
      # activate PROF user 1
    When I click on "admin-homepage"
    And I click on "send-activation-email" in the "user-behat-prof1publicguardiangsigovuk" region
    Then the response status code should be 200
    And the last email containing a link matching "/user/activate/" should have been sent to "behat-prof1@publicguardian.gsi.gov.uk"

  Scenario: PROF user registration steps
    Given emails are sent from "admin" area
    And I go to "/logout"
    And I open the "/user/activate/" link from the email
    # terms
    When I press "agree_terms_save"
    Then the following fields should have an error:
      | agree_terms_agreeTermsUse |
    When I check "agree_terms_agreeTermsUse"
    And I press "agree_terms_save"
    Then the form should be valid
    # password step
    When I fill in the password fields with "Abcd1234"
    And I check "set_password_showTermsAndConditions"
    And I click on "save"
    Then the form should be valid
    # assert pre-fill
    Then the following fields should have the corresponding values:
      | user_details_firstname | DEP1     |
      | user_details_lastname  | SURNAME1 |
    # fill form. Validation is skipepd as already tested in PA scenarios (same page)
    When I fill in the following:
      | user_details_firstname  | John Named           |
      | user_details_lastname   | Green      |
      | user_details_jobTitle   | Solicitor      |
      | user_details_phoneMain  | 10000000001 |
    And I press "user_details_save"
    Then the form should be valid
    And the URL should match "/org"
    # check I'm in the dashboard
    And I should see the "client-01000010" region

  Scenario: Activation link is removed
    Given I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then I should not see "send-activation-email" in the "user-behat-prof1publicguardiangsigovuk" region

  Scenario: Register PROF2 user
    Given emails are sent from "admin" area
    And I reset the email log
    And I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "send-activation-email" in the "user-behat-prof2publicguardiangsigovuk" region
    And I go to "/logout"
    And I open the "/user/activate/" link from the email
    # terms
    When I check "agree_terms_agreeTermsUse"
    And I press "agree_terms_save"
    Then the form should be valid
    # password step
    When I fill in the password fields with "Abcd1234"
    And I check "set_password_showTermsAndConditions"
    And I click on "save"
    Then the form should be valid
    # correct
    When I fill in the following:
      | user_details_firstname  | Pa User     |
      | user_details_lastname   | Two         |
      | user_details_jobTitle   | Solicitor   |
      | user_details_phoneMain  | 20000000001 |
    And I press "user_details_save"
    Then the form should be valid
    # check I'm in the dashboard
    And I should see the "client-02000001" region

  Scenario: Register PROF3 user
    Given emails are sent from "admin" area
    And I reset the email log
    And I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "send-activation-email" in the "user-behat-prof3publicguardiangsigovuk" region
    And I go to "/logout"
    And I open the "/user/activate/" link from the email
    # terms
    When I check "agree_terms_agreeTermsUse"
    And I press "agree_terms_save"
    Then the form should be valid
    # password step
    When I fill in the password fields with "Abcd1234"
    And I check "set_password_showTermsAndConditions"
    When I click on "save"
    Then the form should be valid
    # correct
    And I fill in the following:
      | user_details_firstname  | Pa User     |
      | user_details_lastname   | Three       |
      | user_details_jobTitle   | Solicitor   |
      | user_details_phoneMain  | 30000000001 |
    And I press "user_details_save"
    Then the form should be valid
    # check I'm in the dashboard
    And I should see the "client-03000001" region

  Scenario: Edit PROF2 user
    Given I save the application status into "prof-users-uploaded"
    When I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "user-behat-prof2publicguardiangsigovuk" in the "user-behat-prof2publicguardiangsigovuk" region
    Then the following fields should have the corresponding values:
      | admin_email      | behat-prof2@publicguardian.gsi.gov.uk |
      | admin_firstname  | Pa User                             |
      | admin_lastname   | Two                                 |
      | admin_roleName   | ROLE_PROF_NAMED                       |
    When I fill in the following:
      | admin_email      | behat-prof2-edited@publicguardian.gsi.gov.uk |
      | admin_firstname  | Edited Pa User                             |
      | admin_lastname   | Edited Two                                 |
    And I press "admin_save"
    Then the form should be valid
    When I click on "admin_cancel"
    Then I should not see the "user-behat-prof2publicguardiangsigovuk" region
    And I should see "Edited Pa User Edited Two" in the "user-behat-prof2-editedpublicguardiangsigovuk" region
    And I should see "behat-prof2-edited@publicguardian.gsi.gov.uk" in the "user-behat-prof2-editedpublicguardiangsigovuk" region
    When I go to "/logout"
    # try logging in with the new email
    And I am logged in as "behat-prof2-edited@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then I should see the "client-02000001" region

  Scenario: Edit PROF2 user email to an existing email
    Given I load the application status from "prof-users-uploaded"
    When I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "user-behat-prof2publicguardiangsigovuk" in the "user-behat-prof2publicguardiangsigovuk" region
    And I fill in the following:
      | admin_email      | behat-prof3@publicguardian.gsi.gov.uk |
      | admin_firstname  | Pa User                             |
      | admin_lastname   | Three                               |
    And I press "admin_save"
    Then the following fields should have an error:
      | admin_email |
    When I click on "admin_cancel"
    # edit did not occur due to re used email
    Then I should see the "user-behat-prof2publicguardiangsigovuk" region
    And I should see "Pa User Two" in the "user-behat-prof2publicguardiangsigovuk" region
    And I should see "behat-prof2@publicguardian.gsi.gov.uk" in the "user-behat-prof2publicguardiangsigovuk" region