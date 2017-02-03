Feature: deputy / user / add user

  @deputy
  # remove when ODR-enabled deputy and 103-enabled deputy can be created via registration page
  Scenario: admin login
    Given I am on admin login page
    And I save the page as "admin-login"
      #Then the response status code should be 200
      # test wrong credentials
    When I fill in the following:
      | login_email    | admin@publicguardian.gsi.gov.uk |
      | login_password | WRONG PASSWORD !!               |
    And I click on "login"
    Then I should see an "#error-summary" element
    And I save the page as "admin-login-error1"
      # test user email in caps
    When I fill in the following:
      | login_email    | admin@publicguardian.gsi.gov.uk |
      | login_password | Abcd1234                        |
    And I click on "login"
    Then I should see "admin@publicguardian.gsi.gov.uk" in the "users" region
    Given I am not logged into admin
      # test right credentials
    When I fill in the following:
      | login_email    | admin@publicguardian.gsi.gov.uk |
      | login_password | Abcd1234                        |
    And I click on "login"
      #When I go to "/admin"
    Then I am on admin page "/admin"

  @deputy
  Scenario: add deputy user from registration page
    And emails are sent from "deputy" area
    And I reset the email log
    When I am on "/register"
    And I add the following users to CASREC:
      | Case     | Surname | Deputy No | Dep Surname | Dep Postcode | Typeofrep |
      | BEHAT001 | Hent    | BEHAT001  | Doe         | P0ST C0D3    | OPG102    |
    And I fill in the following:
      | self_registration_firstname       | John                                 |
      | self_registration_lastname        | Doe                                  |
      | self_registration_email_first     | behat-user@publicguardian.gsi.gov.uk |
      | self_registration_email_second    | behat-user@publicguardian.gsi.gov.uk |
      | self_registration_postcode        | P0ST C0D3                            |
      | self_registration_clientFirstname | Cly                                  |
      | self_registration_clientLastname  | Hent                                 |
      | self_registration_caseNumber      | BEHAT001                             |
    And I press "self_registration_save"
    Then I should see "Please check your email"
    And the last email containing a link matching "/user/activate/" should have been sent to "behat-user@publicguardian.gsi.gov.uk"

  @odr
  Scenario: add deputy user (odr)
    Given emails are sent from "admin" area
    And I reset the email log
    And I load the application status from "init"
    And I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
      # assert form OK
    When I create a new "ODR-enabled" "Lay Deputy" user "John ODR" "Doe ODR" with email "behat-user-odr@publicguardian.gsi.gov.uk"
    Then I should see "behat-user-odr@publicguardian.gsi.gov.uk" in the "users" region
    And I should see "yes" in the "behat-user-odrpublicguardiangsigovuk-odr-enabled" region
    And I save the page as "admin-deputy-added"
    And the last email containing a link matching "/user/activate/" should have been sent to "behat-user-odr@publicguardian.gsi.gov.uk"


