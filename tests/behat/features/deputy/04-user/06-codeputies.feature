Feature: Codeputy Self Registration

  @deputy @jack
  Scenario: Codeps setup
    Given I load the application status from "init"
    And I truncate the users from CASREC:
    And emails are sent from "deputy" area
    And I reset the email log
    Given I add the following users to CASREC:
      | Case     | Surname | Deputy No | Dep Surname | Dep Postcode | Typeofrep |
      | 00000000 | Jones   | D000      | Goodby      | AA1 2BB      | OPG102    |
      | 11111111 | Jarvis  | D001      | Goodby      | DY9 0RS      | OPG102    |
      | 11111111 | Jarvis  | D002      | Hale        | DY8 1QR      | OPG102    |
      | 11111111 | Jarvis  | D003      | Lloyd       | B28 9EQ      | OPG102    |
      | 11111111 | Jarvis  | D003      | Smith       | B30 2PT      | OPG102    |

  @deputy
  Scenario: absence of co-deputies section for a client without multiple assigned deputies
    Given emails are sent from "deputy" area
    And I reset the email log
    When I am on "/register"
    And I fill in the following:
      | self_registration_firstname       | Jack                                              |
      | self_registration_lastname        | Goodby                                            |
      | self_registration_email_first     | behat-jack.goodby+noncodep@digital.justice.gov.uk |
      | self_registration_email_second    | behat-jack.goodby+noncodep@digital.justice.gov.uk |
      | self_registration_postcode        | AA1 2BB                                           |
      | self_registration_clientFirstname | Jim                                               |
      | self_registration_clientLastname  | Jones                                             |
      | self_registration_caseNumber      | 00000000                                          |
    And I press "self_registration_save"
    And the last email containing a link matching "/user/activate/" should have been sent to "behat-jack.goodby+noncodep@digital.justice.gov.uk"
    When I open the "/user/activate/" link from the email
    When I fill in the following:
      | set_password_password_first  | Abcd1234 |
      | set_password_password_second | Abcd1234 |
    And I press "set_password_save"
    And I fill in the following:
      | user_details_address1       | Address1     |
      | user_details_addressCountry | GB           |
      | user_details_phoneMain      | 0777 222 333 |
    And I press "user_details_save"
    And I fill in the following:
      | client_firstname       | Fred     |
      | client_courtDate_day   | 01       |
      | client_courtDate_month | 01       |
      | client_courtDate_year  | 2016     |
      | client_address         | address1 |
      | client_country         | GB       |
      | client_postcode        | SW1 1RH  |
    And I press "client_save"
    And I set the report start date to "1/1/2016"
    And I set the report end date to "1/1/2017"
    Then the URL should match "/lay"
    Then I go to "/logout"
    Given I am logged in as "behat-jack.goodby+noncodep@digital.justice.gov.uk" with password "Abcd1234"
    Then the URL should match "/lay"
    And I should not see the "codeputies" region

  @deputy
  Scenario: The first codeputy of a client is able to self register
    And emails are sent from "deputy" area
    And I reset the email log

    # CORRECT
    When I am on "/register"
    And I fill in the following:
      | self_registration_firstname       | Jack                                          |
      | self_registration_lastname        | Goodby                                        |
      | self_registration_email_first     | behat-jack.goodby+mld1@digital.justice.gov.uk |
      | self_registration_email_second    | behat-jack.goodby+mld1@digital.justice.gov.uk |
      | self_registration_postcode        | DY9 0RS                                       |
      | self_registration_clientFirstname | Patricia                                      |
      | self_registration_clientLastname  | Jarvis                                        |
      | self_registration_caseNumber      | 11111111                                      |

    # Incorrect deputy postcode (error displayed inline)
    Given I fill in the following:
      | self_registration_postcode        | MOOMOOO                               |
    When I press "self_registration_save"
    Then the following fields should have an error:
      | self_registration_postcode |
    And I should be on "/register"

    # Incorrect deputy surname (fails casrec check and non specific error displayed)
    Given I fill in the following:
      | self_registration_postcode        | DY9 0RS                               |
      | self_registration_lastname        | Goodby123                             |
    When I press "self_registration_save"
    Then I should see a "#error-summary" element
    And I should be on "/register"

    # Incorrect client surname (fails casrec check and non specific error displayed)
    Given I fill in the following:
      | self_registration_lastname        | Goodby                                |
      | self_registration_clientLastname  | Jarvis123                             |
    When I press "self_registration_save"
    Then I should see a "#error-summary" element
    And I should be on "/register"

    # Incorrect case number (fails casrec check and non specific error displayed)
    Given I fill in the following:
      | self_registration_clientLastname  | Jarvis                             |
      | self_registration_caseNumber      | 12121212                           |
    When I press "self_registration_save"
    Then I should see a "#error-summary" element
    And I should be on "/register"

    # All fields now correct
    Given I fill in the following:
      | self_registration_caseNumber      | 11111111                           |
    When I press "self_registration_save"
    Then the form should be valid
    And I save the page as "codeputy-selfreg-ok"
    Then I should see "Please check your email"
    And I should see "We've sent you a link to behat-jack.goodby+mld1@digital.justice.gov.uk"
    And the last email containing a link matching "/user/activate/" should have been sent to "behat-jack.goodby+mld1@digital.justice.gov.uk"

    # 1st codep registers fully
    When I open the "/user/activate/" link from the email
    Then the response status code should be 200
    When I fill in the following:
      | set_password_password_first  | Abcd1234 |
      | set_password_password_second | Abcd1234 |
    And I press "set_password_save"
      #Then the response status code should be 200
    Then the URL should match "/user/details"
    When I fill in the following:
      | user_details_address1       | Address1     |
      | user_details_addressCountry | GB           |
      | user_details_phoneMain      | 0777 222 333 |
    And I press "user_details_save"
      #Then the response status code should be 200
    Then the URL should match "/client/add"
    Then I fill in the following:
      | client_firstname       | Patricia |
      | client_courtDate_day   | 07       |
      | client_courtDate_month | 07       |
      | client_courtDate_year  | 2016     |
      | client_address         | Address1 |
      | client_country         | GB       |
      | client_postcode        | B12 3CD  |
    And I press "client_save"
    Then the URL should match "/report/create/\d+"
    And I set the report start date to "7/7/2016"
    And I set the report end date to "7/7/2017"
    Then the URL should match "/lay"
    Then I go to "/logout"


  @deputy
  Scenario: The first co-deputy logs in and sees the deputy area and invites a codeputy
    Given emails are sent from "deputy" area
    And I reset the email log
    When I am logged in as "behat-jack.goodby+mld1@digital.justice.gov.uk" with password "Abcd1234"
    Then the URL should match "/lay"
    And I should see the "codeputies" region
    And I click on "invite-codeputy-button"
    Then the URL should match "/codeputy/\d+/add"
    When I fill in the following:
      | co_deputy_invite_email       | behat-jack.goodby+mld2@digital.justice.gov.uk |
    And I press "co_deputy_invite_submit"
    Then the URL should match "/lay"
    And I should see "behat-jack.goodby+mld2@digital.justice.gov.uk" in the "codeputies" region
    And I should see "Awaiting registration" in the "codeputies" region
    And I should see "Edit/Resend invite" in the "codeputies" region
    And the last email containing a link matching "/user/activate/" should have been sent to "behat-jack.goodby+mld2@digital.justice.gov.uk"

  @deputy
  Scenario: The first co-deputy re-invites a deputy (same email address)
    Given emails are sent from "deputy" area
    And I reset the email log
    When I am logged in as "behat-jack.goodby+mld1@digital.justice.gov.uk" with password "Abcd1234"
    And I click on "resend-invite"
    Then the URL should match "/codeputy/\d+/add"
    When I press "co_deputy_invite_submit"
    Then the URL should match "/lay"
    And I should see "behat-jack.goodby+mld2@digital.justice.gov.uk" in the "codeputies" region
    And I should see "Awaiting registration" in the "codeputies" region
    And I should see "Edit/Resend invite" in the "codeputies" region
    And the last email containing a link matching "/user/activate/" should have been sent to "behat-jack.goodby+mld2@digital.justice.gov.uk"