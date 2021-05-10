Feature: User Self Registration

  @deputy
  Scenario: A user can enter their self registration information
    Given I truncate the users from CASREC:
      #
      # Form all empty
      #
    When I am on "/register"
    And I fill in the following:
      | self_registration_firstname       |  |
      | self_registration_lastname        |  |
      | self_registration_email_first     |  |
      | self_registration_email_second    |  |
      | self_registration_postcode        |  |
      | self_registration_clientFirstname |  |
      | self_registration_clientLastname  |  |
      | self_registration_caseNumber      |  |
    And I press "self_registration_save"
    Then I should see a "#error-summary" element
    Then the following fields should have an error:
      | self_registration_firstname       |
      | self_registration_lastname        |
      | self_registration_email_first     |
      | self_registration_clientFirstname |
      | self_registration_clientLastname  |
      | self_registration_caseNumber      |
      #
      # email invalid
      #
    When I am on "/register"
    And I fill in the following:
      | self_registration_firstname       | Zac               |
      | self_registration_lastname        | Tolley            |
      | self_registration_email_first     | aaa@@invalid      |
      | self_registration_email_second    | aaa@@invalid      |
      | self_registration_postcode        |                   |
      | self_registration_clientFirstname | John              |
      | self_registration_clientLastname  | Cross  tolley     |
      | self_registration_caseNumber      | 11112222          |
    And I press "self_registration_save"
    Then I should see a "#error-summary" element
    Then the following fields should have an error:
      | self_registration_email_first |
      #
      # email mismatch
      #
    When I am on "/register"
    And I fill in the following:
      | self_registration_firstname       | Zac                                          |
      | self_registration_lastname        | Tolley                                       |
      | self_registration_email_first     | behat-zac.tolley@digital.justice.gov.uk      |
      | self_registration_email_second    | behat-zac.tolley-diff@digital.justice.gov.uk |
      | self_registration_postcode        |                                              |
      | self_registration_clientFirstname | John                                         |
      | self_registration_clientLastname  | Cross  tolley                                |
      | self_registration_caseNumber      | 11112222                                     |
    And I press "self_registration_save"
    Then I should see a "#error-summary" element
    Then the following fields should have an error:
      | self_registration_email_first |
      #
      # CASREC mismatch
      #
    When I am on "/register"
    And I fill in the following:
      | self_registration_firstname       | Zac                                     |
      | self_registration_lastname        | Tolley                                  |
      | self_registration_email_first     | behat-zac.tolley@digital.justice.gov.uk |
      | self_registration_email_second    | behat-zac.tolley@digital.justice.gov.uk |
      | self_registration_postcode        |                                         |
      | self_registration_clientFirstname | John                                    |
      | self_registration_clientLastname  | Cross  tolley                           |
      | self_registration_caseNumber      | 11112222                                |
    And I press "self_registration_save"
    Then I should see a "#error-summary" element
        And I should be on "/register"
      #
      # Postcode mismatch
      #
    Given I add the following users to CASREC:
      | Case     | Surname       | Deputy No | Dep Surname | Dep Postcode | Typeofrep |
      | 11112222 | Cross-Tolley  | D001      | Tolley      | SW1 3RF      | OPG102    |
      | 11113333 | Cross-Tolley2 | D002      | Tolley2     | SW1 3RF2     | OPG102    |
    And I press "self_registration_save"
    Then I should see a "#error-summary" element
          #
      # success (by fixing postcode)
      #
    And I fill in the following:
      | self_registration_postcode | SW1 3RF |
    And I press "self_registration_save"
    Then the form should be valid
    Then I should see "Please check your email"
    And I should see "We've sent you a link to behat-zac.tolley@digital.justice.gov.uk"
      #
      # check user is created
      #
    Then I am on admin login page
    And I fill in the following:
      | login_email    | admin@publicguardian.gov.uk |
      | login_password | DigidepsPass1234                        |
    Then I click on "login"
    Then I should see "behat-zac.tolley@digital.justice.gov.uk" in the "users" region

  @deputy
  Scenario: Inform the use that Someone else has already registered with this case number
    Given I am on "/register"
    And I fill in the following:
      | self_registration_firstname       | Zac                                         |
      | self_registration_lastname        | Tolley                                      |
      | self_registration_email_first     | behat-zac.tolley-new@digital.justice.gov.uk |
      | self_registration_email_second    | behat-zac.tolley-new@digital.justice.gov.uk |
      | self_registration_postcode        | SW1 3RF                                     |
      | self_registration_clientFirstname | John                                        |
      | self_registration_clientLastname  | Cross-Tolley                                |
      # add case number already used
      | self_registration_caseNumber      | 11112222                                    |
    And I press "self_registration_save"
    Then I should see a "#error-summary" element

  @deputy
  Scenario: Inform the use that email already exists
    Given I am on "/register"
    And I fill in the following:
      | self_registration_firstname       | Zac2                                        |
      | self_registration_lastname        | Tolley2                                     |
      | self_registration_email_first     | behat-zac.tolley-dup@digital.justice.gov.uk |
      | self_registration_email_second    | behat-zac.tolley-dup@digital.justice.gov.uk |
      | self_registration_postcode        | SW1 3RF2                                    |
      | self_registration_clientFirstname | John                                        |
      | self_registration_clientLastname  | Cross-Tolley2                               |
      | self_registration_caseNumber      | 11113333                                    |
    And I press "self_registration_save"
    Then I should see "Please check your email"
    Given I am on "/register"
    And I fill in the following:
      | self_registration_firstname       | Zac                                         |
      | self_registration_lastname        | Tolley                                      |
      | self_registration_email_first     | behat-zac.tolley-dup@digital.justice.gov.uk |
      | self_registration_email_second    | behat-zac.tolley-dup@digital.justice.gov.uk |
      | self_registration_postcode        | SW1 3RF                                     |
      | self_registration_clientFirstname | John                                        |
      | self_registration_clientLastname  | Cross-Tolley                                |
      | self_registration_caseNumber      | 11112222                                    |
    And I press "self_registration_save"
    Then the form should be invalid

  @deputy
  Scenario: A user can self register and activate
    Given I add the following users to CASREC:
      | Case     | Surname      | Deputy No | Dep Surname | Dep Postcode | Typeofrep |
      | 11112233 | Cross-Lens   | D001      | Lens      | SW1 3RF      | OPG102    |
    And I am on "/register"
    And I fill in the following:
      | self_registration_firstname       | Jenny                                     |
      | self_registration_lastname        | Lens                                  |
      | self_registration_email_first     | behat-jenny.lens@digital.justice.gov.uk |
      | self_registration_email_second    | behat-jenny.lens@digital.justice.gov.uk |
      | self_registration_postcode        | SW1 3RF                                 |
      | self_registration_clientFirstname | John                                    |
      | self_registration_clientLastname  | Cross-Lens                              |
      | self_registration_caseNumber      | 11112233                                |
    And I press "self_registration_save"
    Then I should see "Please check your email"
    And I should see "We've sent you a link to behat-jenny.lens@digital.justice.gov.uk"
    When I activate the user "behat-jenny.lens@digital.justice.gov.uk" with password "DigidepsPass1234"
    Then the URL should match "/login"
    When I am logged in as "behat-jenny.lens@digital.justice.gov.uk" with password "DigidepsPass1234"
    Then the URL should match "/user/details"
    When I fill in the following:
      | user_details_address1       | Address1     |
      | user_details_addressCountry | GB           |
      | user_details_phoneMain      | 0777 222 333 |
    And I press "user_details_save"
      #Then the response status code should be 200
    Then the URL should match "/client/add"
    Then I fill in the following:
      | client_firstname       | Fred     |
      | client_courtDate_day   | 01       |
      | client_courtDate_month | 01       |
      | client_courtDate_year  | 2016     |
      | client_address         | address1 |
      | client_country         | GB       |
      | client_postcode        | SW1 1RH  |
    And I press "client_save"
    Then the URL should match "/report/create/\d+"
    And I set the report start date to "1/1/2016"
    And I set the report end date to "1/1/2016"
    Then the URL should match "/lay"
    Then I go to "/logout"
    And I am logged in as "behat-jenny.lens@digital.justice.gov.uk" with password "DigidepsPass1234"
    Then the URL should match "/lay"
