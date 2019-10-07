Feature: PA cannot access other's PA's reports and clients
# team1 = team with client 1000010
# team2 = team with client 2000003

  Scenario: PA reload status from the point where team1 has been fully added
    Given I load the application status from "team-users-complete"

  Scenario: Assert team1 can only access its reports
    # Named PA
    Given I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-01000010" region
    Then the response status code should be 200
    And the URL should match "report/\d+/overview"
    And I save the current URL as "report-for-client-01000010.url"
    But I should not see the "client-02000003" region
    # Admin
    Given I am logged in as "behat-pa1-admin@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-01000010" region
    Then the response status code should be 200
    And the current URL should match with the URL previously saved as "report-for-client-01000010.url"
    But I should not see the "client-02000003" region
    # team member
    Given I am logged in as "behat-pa1-team-member@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-01000010" region
    Then the response status code should be 200
    And the current URL should match with the URL previously saved as "report-for-client-01000010.url"
    But I should not see the "client-02000003" region

  Scenario: team2 can access its client but not team1's data
    # can access team2 reports
    Given I am logged in as "behat-pa2@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-02000001" region
    Then the response status code should be 200
    And the URL should match "report/\d+/overview"
    # cannot access team1 reports
    But I should not see the "client-01000010" region
    When I go to the URL previously saved as "report-for-client-01000010.url"
    Then the response status code should be 500

  Scenario: PA user cannot edit client
    Given I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
    Then the URL "/deputyship-details" should be forbidden
    And the URL "/deputyship-details/your-client" should be forbidden
    And the URL "/deputyship-details/your-client/edit" should be forbidden
    And the URL "/deputyship-details/your-details" should be forbidden
    And the URL "/deputyship-details/your-details/edit" should be forbidden
    And the URL "/deputyship-details/your-details/change-password" should be forbidden

  Scenario: Submitted reports cannot be viewed (overview page) or edited
    # load "pre-submission" status and save links
    Given I load the application status from "pa-report-completed"
    And I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "pa-report-open" in the "client-01000014" region
    And I save the current URL as "client-01000014-report-overview"
    And I click on "edit-report-period"
    Then the response status code should be 200
    # load "after submission" status and re-check the same links
    And I save the current URL as "client-01000014-report-completed"
    When I load the application status from "pa-report-submitted"
    When I go to the URL previously saved as "client-01000014-report-overview"
    Then the response status code should be 500
    When I go to the URL previously saved as "client-01000014-report-completed"
    Then the response status code should be 500

  Scenario: PA_ADMIN logs in, edits own account and removes admin privilege should be logged out
    Given I load the application status from "team-users-complete"
    And I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "org-settings, user-accounts"
    When I click on "edit" in the "team-user-behat-pa1-adminpublicguardiangovuk" region
    And I fill in the following:
      | team_member_account_roleName_1 | ROLE_PA_TEAM_MEMBER                             |
    And I press "team_member_account_save"
    Then the form should be valid
    And the response status code should be 200
    And I go to "/logout"

  Scenario: PA_ADMIN logs in, edits own account keeps admin privilege should remain logged in
    Given I load the application status from "team-users-complete"
    And I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
    When I click on "org-settings, user-accounts"
    When I click on "edit" in the "team-user-behat-pa1-adminpublicguardiangovuk" region
    And I fill in the following:
      | team_member_account_firstname  | edit                                             |
    And I press "team_member_account_save"
    Then the form should be valid
    And the response status code should be 200
    And I go to "/org/team"

  Scenario: CSV org-upload
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    # upload PA users
    When I click on "admin-upload-pa"
    And I attach the file "behat-pa-orgs.csv" to "admin_upload_file"
    And I press "admin_upload_upload"
    Then the form should be valid

  Scenario: Admin activates PA Org 1 deputy
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And emails are sent from "admin" area
    # activate PA Org 1 user
    When I click on "admin-homepage"
    And I click on "send-activation-email" in the "user-behat-pa-org1pa-org1govuk" region
    Then the response status code should be 200
    And the last email containing a link matching "/user/activate/" should have been sent to "behat-pa-org1@pa-org1.gov.uk"
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
    When I fill in the following:
      | user_details_jobTitle   | Case worker      |
      | user_details_phoneMain  | 40000000001 |
    And I press "user_details_save"
    Then the form should be valid

  Scenario: Admin activates PA Org 2 deputy
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And emails are sent from "admin" area
   # activate PA Org 1 user
    When I click on "admin-homepage"
    And I click on "send-activation-email" in the "user-behat-pa-org2pa-org2govuk" region
    Then the response status code should be 200
    And the last email containing a link matching "/user/activate/" should have been sent to "behat-pa-org2@pa-org2.gov.uk"
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
    When I fill in the following:
      | user_details_jobTitle   | Case worker      |
      | user_details_phoneMain  | 40000000002 |
    And I press "user_details_save"
    Then the form should be valid

  Scenario: PA Org 1 can access own reports and clients
    Given I am logged in as "behat-pa-org1@pa-org1.gov.uk" with password "Abcd1234"
    # access report and save for future feature tests
    Then I click on "pa-report-open" in the "client-40000041" region
    And I save the report as "40000041-report"
    And I click on "client-edit"
    And the response status code should be 200
    And I save the current URL as "client-40000041-edit"
    Then I go to "/logout"

  Scenario: PA Org 2 can access own reports and clients
    Given I am logged in as "behat-pa-org2@pa-org2.gov.uk" with password "Abcd1234"
    # access report and save for future feature tests
    Then I click on "pa-report-open" in the "client-40000042" region
    And I save the report as "40000042-report"
    And I click on "client-edit"
    And the response status code should be 200
    And I save the current URL as "client-40000042-edit"
    Then I go to "/logout"

  Scenario: PA Org 1 user logs in and should only see their clients and reports (from the existing team structure)
    Given I am logged in as "behat-pa-org1@pa-org1.gov.uk" with password "Abcd1234"
    # check I'm in the dashboard and I see only my own client
    And I should see the "client-40000041" region
    And I should not see the "client-40000042" region
    Then I go to the report URL "overview" for "40000042-report"
    And the response status code should be 500
    Then I go to the URL previously saved as "client-40000042-edit"
    And the response status code should be 500

  Scenario: Admin adds PA org 2 deputy to PA Org 1 but org 1 does not get PA org 2's clients
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    When I go to admin page "/admin/organisations"
    And I follow "behat-pa-org1@pa-org1.gov.uk"
    And I follow "Add someone to this organisation"
    And I fill in "organisation_add_user_email" with "behat-pa-org2@pa-org2.gov.uk"
    And I press "Find user"
    And I press "Add user to organisation"
    # Check org shows org 1's client but does not show org 2's own client
    And I should see the "org-40000041" region
    And I should not see the "org-40000042" region

  Scenario: PA org 1 deputy logs in and should still only access their own client (from existing team structure)
    Given I am logged in as "behat-pa-org1@pa-org1.gov.uk" with password "Abcd1234"
    Then I should see the "client-40000041" region
    And I should not see the "client-40000042" region
    Then I go to the report URL "overview" for "40000041-report"
    And the response status code should be 200
    Then I go to the URL previously saved as "client-40000041-edit"
    And the response status code should be 200
    Then I go to the report URL "overview" for "40000042-report"
    And the response status code should be 500
    Then I go to the URL previously saved as "client-40000042-edit"
    And the response status code should be 500

  Scenario: PA org 1 is activated
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I go to admin page "/admin/organisations"
    When I click on "edit" in the "org-behat-pa-org1pa-org1govuk" region
    And I fill in "organisation_isActivated_0" with "1"
    And I press "Save organisation"

  Scenario: PA org 1 deputy logs in and should now see their existing client (from existing team structure) but not org 2's client
    # log in shown in PA dashboard
    Given I am logged in as "behat-pa-org1@pa-org1.gov.uk" with password "Abcd1234"
    Then I should see the "client-40000041" region
    And I should not see the "client-40000042" region
    Then I go to the report URL "overview" for "40000041-report"
    And the response status code should be 200
    Then I go to the report URL "overview" for "40000042-report"
    And the response status code should be 500

  Scenario: PA org 2 deputy logs in and should now see their existing client (from existing team structure) and new org 1 client
    # log in shown in PA dashboard
    Given I am logged in as "behat-pa-org2@pa-org2.gov.uk" with password "Abcd1234"
    Then I should see the "client-40000041" region
    And I should see the "client-40000042" region
    Then I go to the report URL "overview" for "40000041-report"
    And the response status code should be 200
    Then I go to the report URL "overview" for "40000042-report"
    And the response status code should be 200


  # Activate Org 2 should not change anything
  Scenario: PA org 2 is activated
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I go to admin page "/admin/organisations"
    When I click on "edit" in the "org-behat-pa-org2pa-org2govuk" region
    And I fill in "organisation_isActivated_0" with "1"
    And I press "Save organisation"

  Scenario: PA org 1 deputy logs in and should STILL see their existing client (from existing team structure) but NOT org 2's client
    # log in shown in PA dashboard
    Given I am logged in as "behat-pa-org1@pa-org1.gov.uk" with password "Abcd1234"
    Then I should see the "client-40000041" region
    And I should not see the "client-40000042" region
    Then I go to the report URL "overview" for "40000041-report"
    And the response status code should be 200
    Then I go to the report URL "overview" for "40000042-report"
    And the response status code should be 500

  Scenario: PA org 2 deputy logs in and should STILL see their existing client (from existing team structure) and new org 1 client
    # log in shown in PA dashboard
    Given I am logged in as "behat-pa-org2@pa-org2.gov.uk" with password "Abcd1234"
    Then I should see the "client-40000041" region
    And I should see the "client-40000042" region
    Then I go to the report URL "overview" for "40000041-report"
    And the response status code should be 200
    Then I go to the report URL "overview" for "40000042-report"
    And the response status code should be 200
