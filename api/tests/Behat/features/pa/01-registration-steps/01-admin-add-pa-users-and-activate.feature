Feature: Add PA users and activate PA user (journey)
  Scenario: Activate PA user
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
      # upload PA users
    When I go to admin page "/admin/org-csv-upload"
    When I attach the file "behat-pa.csv" to "admin_upload_file"
    And I press "admin_upload_upload"
    Then the form should be valid
    When I follow meta refresh
    And I click on "admin-homepage"
    And I click on "send-activation-email" in the "user-behat-pa1publicguardiangovuk" region
    Then the response status code should be 200

  Scenario: PA user registration steps
    When I open the activation page for "behat-pa1@publicguardian.gov.uk"
    # password step
    When I fill in the password fields with "DigidepsPass1234"
    And I check "set_password_showTermsAndConditions"
    And I click on "save"
    Then the form should be valid
    And the url should match "/login"
    And I should see "Sign in to your new account"
    When I am logged in as "behat-pa1@publicguardian.gov.uk" with password "DigidepsPass1234"
    # assert pre-fill
    Then the url should match "/user/details"
    And the following fields should have the corresponding values:
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
    # correct
    When I fill in the following:
      | user_details_firstname  | John Named           |
      | user_details_lastname   | Green      |
      | user_details_jobTitle   | Solicitor      |
      | user_details_phoneMain  | 10000000001 |
    And I press "user_details_save"
    Then the form should be valid
    # check I'm in the dashboard
    And I should see the "client-02100010" region

  Scenario: Activation link is removed
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    Then I should not see "send-activation-email" in the "user-behat-pa1publicguardiangovuk" region

  Scenario: Register PA2 user
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "send-activation-email" in the "user-behat-pa2publicguardiangovuk" region
    And I go to "/logout"
    When I open the activation page for "behat-pa2@publicguardian.gov.uk"
    # password step
    When I fill in the password fields with "DigidepsPass1234"
    And I check "set_password_showTermsAndConditions"
    And I click on "save"
    Then the form should be valid
    When I am logged in as "behat-pa2@publicguardian.gov.uk" with password "DigidepsPass1234"
    # correct
    And I fill in the following:
      | user_details_firstname  | Pa User     |
      | user_details_lastname   | Two         |
      | user_details_jobTitle   | Solicitor   |
      | user_details_phoneMain  | 20000000001 |
    And I press "user_details_save"
    Then the form should be valid
    # check I'm in the dashboard
    And I should see the "client-02200001" region

  Scenario: Register PA3 user
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "send-activation-email" in the "user-behat-pa3publicguardiangovuk" region
    And I go to "/logout"
    When I open the activation page for "behat-pa3@publicguardian.gov.uk"
    # password step
    When I fill in the password fields with "DigidepsPass1234"
    And I check "set_password_showTermsAndConditions"
    When I click on "save"
    Then the form should be valid
    When I am logged in as "behat-pa3@publicguardian.gov.uk" with password "DigidepsPass1234"
    # correct
    And I fill in the following:
      | user_details_firstname  | Pa User     |
      | user_details_lastname   | Three       |
      | user_details_jobTitle   | Solicitor   |
      | user_details_phoneMain  | 30000000001 |
    And I press "user_details_save"
    Then the form should be valid
    # check I'm in the dashboard
    And I should see the "client-02300001" region

  Scenario: Edit PA2 user
    When I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "user-behat-pa2publicguardiangovuk" in the "user-behat-pa2publicguardiangovuk" region
    And I press "Edit user"
    Then the following fields should have the corresponding values:
      | admin_firstname      | Pa User                         |
      | admin_lastname       | Two                             |
    When I fill in the following:
      | admin_firstname  | Edited Pa User                             |
      | admin_lastname   | Edited Two                                 |
    And I press "admin_save"
    Then the form should be valid
    When I click on "admin_cancel"
    And I should see "Edited Pa User Edited Two" in the "user-behat-pa2publicguardiangovuk" region

  Scenario: Ensure all team members are in the same org
    Given the "PA OPG" organisation is activated
    Given the following users are in the organisations:
      | userEmail                         | orgName |
      | behat-pa1@publicguardian.gov.uk   | PA OPG  |
      | behat-pa2@publicguardian.gov.uk   | PA OPG  |
      | behat-pa3@publicguardian.gov.uk   | PA OPG  |

    Given the following users clients are in the users organisation:
      | userEmail                       |
      | behat-pa1@publicguardian.gov.uk |
      | behat-pa2@publicguardian.gov.uk |
      | behat-pa3@publicguardian.gov.uk |
