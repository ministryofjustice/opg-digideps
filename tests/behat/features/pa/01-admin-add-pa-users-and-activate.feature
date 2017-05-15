Feature: Add PA users and activate PA user (journey)

  Scenario: add PA users
    Given I load the application status from "init-pa"
    And emails are sent from "admin" area
    And I reset the email log
    And I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
      # upload PA users
    When I click on "admin-upload-pa"
    When I attach the file "behat-pa.csv" to "admin_upload_file"
    And I press "admin_upload_upload"
    Then the form should be valid
    #Then I should see "Added 1 PA users"
      # activate PA user 1
    When I click on "admin-homepage"
    And I click on "send-activation-email" in the "user-behat-pa1publicguardiangsigovuk" region
    Then the response status code should be 200
    And the last email containing a link matching "/user/activate/" should have been sent to "behat-pa1@publicguardian.gsi.gov.uk"

  Scenario: PA user registration steps
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
    When I fill in the following:
      | set_password_password_first  | Abcd1234 |
      | set_password_password_second | Abcd1234 |
    When I click on "save"
    Then the form should be valid
    # assert pre-fill
    Then the following fields should have the corresponding values:
      | user_details_firstname | DEP1     |
      | user_details_lastname  | SURNAME1 |
    # check errors
    When I fill in the following:
      | user_details_firstname  |  |
      | user_details_lastname   |  |
      | user_details_jobTitle   |  |
      | user_details_phoneMain  |  |
    And I press "user_details_save"
    Then the following fields should have an error:
      | user_details_firstname |
      | user_details_lastname  |
      | user_details_jobTitle  |
      | user_details_phoneMain |
    # correct
    When I fill in the following:
      | user_details_firstname  | John Named           |
      | user_details_lastname   | Green      |
      | user_details_jobTitle   | Solicitor      |
      | user_details_phoneMain  | 10000000001 |
    And I press "user_details_save"
    Then the form should be valid
    # check I'm in the dashboard
    And I should see the "client-1000010" region

  Scenario: Activation link is removed
    Given I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then I should not see "send-activation-email" in the "user-behat-pa1publicguardiangsigovuk" region

  Scenario: Register PA2 user
    Given emails are sent from "admin" area
    And I reset the email log
    And I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "send-activation-email" in the "user-behat-pa2publicguardiangsigovuk" region
    And I go to "/logout"
    And I open the "/user/activate/" link from the email
    # terms
    When I check "agree_terms_agreeTermsUse"
    And I press "agree_terms_save"
    Then the form should be valid
    # password step
    When I fill in the following:
      | set_password_password_first  | Abcd1234 |
      | set_password_password_second | Abcd1234 |
    When I click on "save"
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
    And I should see the "client-2000001" region

  Scenario: Register PA3 user
    Given emails are sent from "admin" area
    And I reset the email log
    And I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "send-activation-email" in the "user-behat-pa3publicguardiangsigovuk" region
    And I go to "/logout"
    And I open the "/user/activate/" link from the email
    # terms
    When I check "agree_terms_agreeTermsUse"
    And I press "agree_terms_save"
    Then the form should be valid
    # password step
    When I fill in the following:
      | set_password_password_first  | Abcd1234 |
      | set_password_password_second | Abcd1234 |
    When I click on "save"
    Then the form should be valid
    # correct
    When I fill in the following:
      | user_details_firstname  | Pa User     |
      | user_details_lastname   | Three       |
      | user_details_jobTitle   | Solicitor   |
      | user_details_phoneMain  | 30000000001 |
    And I press "user_details_save"
    Then the form should be valid
    # check I'm in the dashboard
    And I should see the "client-3000001" region

  Scenario: Edit PA2 user
    Given I save the application status into "pa-users-uploaded"
    When I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "user-behat-pa2publicguardiangsigovuk" in the "user-behat-pa2publicguardiangsigovuk" region
    Then the following fields should have the corresponding values:
      | admin_email      | behat-pa2@publicguardian.gsi.gov.uk |
      | admin_firstname  | Pa User                             |
      | admin_lastname   | Two                                 |
      | admin_roleName   | ROLE_PA                             |
    When I fill in the following:
      | admin_email      | behat-pa2-edited@publicguardian.gsi.gov.uk |
      | admin_firstname  | Edited Pa User                             |
      | admin_lastname   | Edited Two                                 |
    And I press "admin_save"
    Then the form should be valid
    When I click on "admin_cancel"
    Then I should not see the "user-behat-pa2publicguardiangsigovuk" region
    And I should see "Edited Pa User Edited Two" in the "user-behat-pa2-editedpublicguardiangsigovuk" region
    And I should see "behat-pa2-edited@publicguardian.gsi.gov.uk" in the "user-behat-pa2-editedpublicguardiangsigovuk" region
    When I go to "/logout"
    # try logging in with the new email
    And I am logged in as "behat-pa2-edited@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then I should see the "client-2000001" region

  Scenario: Edit PA2 user email to an existing email
    Given I load the application status from "pa-users-uploaded"
    When I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "user-behat-pa2publicguardiangsigovuk" in the "user-behat-pa2publicguardiangsigovuk" region
    And I fill in the following:
      | admin_email      | behat-pa3@publicguardian.gsi.gov.uk |
      | admin_firstname  | Pa User                             |
      | admin_lastname   | Three                               |
    And I press "admin_save"
    Then the following fields should have an error:
      | admin_email |
    When I click on "admin_cancel"
    # edit did not occur due to re used email
    Then I should see the "user-behat-pa2publicguardiangsigovuk" region
    And I should see "Pa User Two" in the "user-behat-pa2publicguardiangsigovuk" region
    And I should see "behat-pa2@publicguardian.gsi.gov.uk" in the "user-behat-pa2publicguardiangsigovuk" region