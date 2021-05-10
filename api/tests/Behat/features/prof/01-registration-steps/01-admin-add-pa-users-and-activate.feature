Feature: Add PROF users and activate PROF user (journey)

  Scenario: Activate Prof user
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
       # upload PROF users
    When I go to admin page "/admin/org-csv-upload"
    When I attach the file "behat-prof.csv" to "admin_upload_file"
    And I press "admin_upload_upload"
    Then the form should be valid
    #Then I should see "Added 1 PROF users"
      # activate PROF user 1
    When I follow meta refresh
    And I click on "admin-homepage"
    And I click on "send-activation-email" in the "user-behat-prof1publicguardiangovuk" region
    Then the response status code should be 200

  Scenario: PROF user registration steps
    When I open the activation page for "behat-prof1@publicguardian.gov.uk"
    # terms
    And I press "agree_terms_save"
    Then the following fields should have an error:
      | agree_terms_agreeTermsUse |
    When I check "agree_terms_agreeTermsUse"
    And I press "agree_terms_save"
    Then the form should be valid
    # password step
    When I fill in the password fields with "DigidepsPass1234"
    And I check "set_password_showTermsAndConditions"
    And I click on "save"
    Then the form should be valid
    And the url should match "/login"
    And I should see "Sign in to your new account"
    When I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
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
    And I should see the "client-31000010" region

  Scenario: Activation link is removed
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    Then I should not see "send-activation-email" in the "user-behat-prof1publicguardiangovuk" region

  Scenario: Register PROF2 user
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "send-activation-email" in the "user-behat-prof2publicguardiangovuk" region
    And I go to "/logout"
    When I open the activation page for "behat-prof2@publicguardian.gov.uk"
    # terms
    When I check "agree_terms_agreeTermsUse"
    And I press "agree_terms_save"
    Then the form should be valid
    # password step
    When I fill in the password fields with "DigidepsPass1234"
    And I check "set_password_showTermsAndConditions"
    And I click on "save"
    Then the form should be valid
    When I am logged in as "behat-prof2@publicguardian.gov.uk" with password "DigidepsPass1234"
    # correct
    When I fill in the following:
      | user_details_firstname  | Pa User     |
      | user_details_lastname   | Two         |
      | user_details_jobTitle   | Solicitor   |
      | user_details_phoneMain  | 20000000001 |
    And I press "user_details_save"
    Then the form should be valid
    # check I'm in the dashboard
    And I should see the "client-32000001" region

  Scenario: Register PROF3 user
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "send-activation-email" in the "user-behat-prof3publicguardiangovuk" region
    And I go to "/logout"
    When I open the activation page for "behat-prof3@publicguardian.gov.uk"
    # terms
    When I check "agree_terms_agreeTermsUse"
    And I press "agree_terms_save"
    Then the form should be valid
    # password step
    When I fill in the password fields with "DigidepsPass1234"
    And I check "set_password_showTermsAndConditions"
    When I click on "save"
    Then the form should be valid
    When I am logged in as "behat-prof3@publicguardian.gov.uk" with password "DigidepsPass1234"
    # correct
    And I fill in the following:
      | user_details_firstname  | Pa User     |
      | user_details_lastname   | Three       |
      | user_details_jobTitle   | Solicitor   |
      | user_details_phoneMain  | 30000000001 |
    And I press "user_details_save"
    Then the form should be valid
    # check I'm in the dashboard
    And I should see the "client-33000001" region

  Scenario: Register PROF4 user
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I create a new "NDR-disabled" "Prof Named" user "ABC org" "Administrator" with email "behat-prof-org-1@org-1.co.uk" and postcode "SW1"
    And "behat-prof-org-1@org-1.co.uk" has been added to the "org-1.co.uk" organisation
    And I add the client with case number "03000025" to be deputised by email "behat-prof-org-1@org-1.co.uk"
    And I go to "/logout"
    When I open the activation page for "behat-prof-org-1@org-1.co.uk"
    When I check "agree_terms_agreeTermsUse"
    And I press "agree_terms_save"
    Then the form should be valid
    When I fill in the password fields with "DigidepsPass1234"
    And I check "set_password_showTermsAndConditions"
    When I click on "save"
    Then the form should be valid
    When I am logged in as "behat-prof-org-1@org-1.co.uk" with password "DigidepsPass1234"
    And I fill in the following:
      | user_details_firstname  | Prof User   |
      | user_details_lastname   | Three       |
      | user_details_jobTitle   | Solicitor   |
      | user_details_phoneMain  | 30000000001 |
    And I press "user_details_save"
    Then the form should be valid

  Scenario: Register PROF5 user
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I create a new "NDR-disabled" "Prof Named" user "ABC org" "Administrator" with email "behat-prof-org-2@org-1.co.uk" and postcode "SW1"
    And I add the client with case number "03000026" to be deputised by email "behat-prof-org-2@org-1.co.uk"
    And I click on "send-activation-email" in the "user-behat-prof-org-2org-1couk" region
    And I go to "/logout"
    When I open the activation page for "behat-prof-org-2@org-1.co.uk"
    And I check "agree_terms_agreeTermsUse"
    And I press "agree_terms_save"
    Then the form should be valid
    When I fill in the password fields with "DigidepsPass1234"
    And I check "set_password_showTermsAndConditions"
    When I click on "save"
    Then the form should be valid
    When I am logged in as "behat-prof-org-2@org-1.co.uk" with password "DigidepsPass1234"
    And I fill in the following:
      | user_details_firstname  | Prof User   |
      | user_details_lastname   | Three       |
      | user_details_jobTitle   | Solicitor   |
      | user_details_phoneMain  | 30000000001 |
    And I press "user_details_save"
    Then the form should be valid

  Scenario: Register PROF6 user
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I create a new "NDR-disabled" "Prof Named" user "ABC org" "Administrator" with email "behat-prof-org-3@org-2.co.uk" and postcode "SW1"
    And I add the client with case number "03000027" to be deputised by email "behat-prof-org-3@org-2.co.uk"
    And I add the client with case number "03000028" to be deputised by email "behat-prof-org-3@org-2.co.uk"
    And I click on "send-activation-email" in the "user-behat-prof-org-3org-2couk" region
    And I go to "/logout"
    When I open the activation page for "behat-prof-org-3@org-2.co.uk"
    And I check "agree_terms_agreeTermsUse"
    And I press "agree_terms_save"
    Then the form should be valid
    When I fill in the password fields with "DigidepsPass1234"
    And I check "set_password_showTermsAndConditions"
    When I click on "save"
    Then the form should be valid
    When I am logged in as "behat-prof-org-3@org-2.co.uk" with password "DigidepsPass1234"
    And I fill in the following:
      | user_details_firstname  | Prof User   |
      | user_details_lastname   | Three       |
      | user_details_jobTitle   | Solicitor   |
      | user_details_phoneMain  | 30000000001 |
    And I press "user_details_save"
    Then the form should be valid

  Scenario: Edit PROF2 user
    Given I save the application status into "prof-users-uploaded"
    When I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "user-behat-prof2publicguardiangovuk" in the "user-behat-prof2publicguardiangovuk" region
    And I press "Edit user"
    Then the following fields should have the corresponding values:
      | admin_firstname      | Pa User                           |
      | admin_lastname       | Two                               |
    When I fill in the following:
      | admin_firstname  | Edited Pa User                             |
      | admin_lastname   | Edited Two                                 |
    And I press "admin_save"
    Then the form should be valid
    When I click on "admin_cancel"
    And I should see "Edited Pa User Edited Two" in the "user-behat-prof2publicguardiangovuk" region

  Scenario: Ensure all team members are in the same org
    Given the following organisations exist:
      | name     | emailIdentifier | activated |
      | PROF OPG | @prof.opg       | true      |

    Given the following users are in the organisations:
      | userEmail                         | orgName  |
      | behat-prof1@publicguardian.gov.uk | PROF OPG |
      | behat-prof2@publicguardian.gov.uk | PROF OPG |
      | behat-prof3@publicguardian.gov.uk | PROF OPG |

    Given the following users clients are in the users organisation:
      | userEmail                         |
      | behat-prof1@publicguardian.gov.uk |
      | behat-prof2@publicguardian.gov.uk |
      | behat-prof3@publicguardian.gov.uk |
