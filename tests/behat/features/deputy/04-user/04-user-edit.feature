Feature: deputy / report / edit user
    
    @deputy
    Scenario: edit user details
        Given I load the application status from "report-submit-pre"
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "user-account, deputy-show, deputy-edit"
        Then I should be on "user-account/user-edit"
        Then the following fields should have the corresponding values:
             | user_details_firstname | John |
             | user_details_lastname | Doe |
             | user_details_address1 | 102 Petty France |
             | user_details_address2 | MOJ |
             | user_details_address3 | London |
             | user_details_addressPostcode | SW1H 9AJ |
             | user_details_addressCountry | GB |
             | user_details_phoneMain | 020 3334 3555  |
             | user_details_phoneAlternative | 020 1234 5678  |
        When I fill in the following:
            | user_details_firstname |  |
            | user_details_lastname |  |
            | user_details_address1 | |
            | user_details_addressPostcode | |
            | user_details_addressCountry | |
            | user_details_phoneMain |   |
        And I press "user_details_save"
        Then the following fields should have an error:
            | user_details_firstname |
            | user_details_lastname |
            | user_details_address1 |
            | user_details_addressPostcode |
            | user_details_addressCountry |
            | user_details_phoneMain |
        And I press "user_details_save"
        Then the form should be invalid
        When I fill in the following:
           | user_details_firstname | Paul |
           | user_details_lastname | Jamie |
           | user_details_address1 | 103 Petty France |
           | user_details_address2 | MOJDS |
           | user_details_address3 | London |
           | user_details_addressPostcode | SW1H 9AA |
           | user_details_addressCountry | GB |
           | user_details_phoneMain | 020 3334 3556  |
           | user_details_phoneAlternative | 020 1234 5679  |
        And I press "user_details_save"
        Then the form should be valid
        And I should be on "/user-account/user-show"
        And I should see "Paul Jamie" in the "my-details-name" region
        And I should see "SW1H 9AA" in the "my-details-address" region
     
  
    @deputy   
    Scenario: change user password
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "user-account, change-password"
        # wrong old password
        When I fill in "change_password_current_password" with "this.is.the.wrong.password"
        And I press "change_password_save"
        Then the following fields should have an error:
              | change_password_current_password |
              | change_password_plain_password_first |
        # invalid new password
        When I fill in the following:
          | change_password_current_password | Abcd1234 |
          | change_password_plain_password_first | 1 |
          | change_password_plain_password_second | 2 |
        And I press "change_password_save"
        Then the following fields should have an error:
              | change_password_plain_password_first |      
        # unmatching new passwords
        When I fill in the following:
          | change_password_current_password | Abcd1234 |
          | change_password_plain_password_first | Abcd1234 |
          | change_password_plain_password_second | Abcd12345 |
        And I press "change_password_save"
        Then the following fields should have an error:
              | change_password_plain_password_first |
        #empty password
        When I fill in the following:
          | change_password_current_password | Abcd1234 |
          | change_password_plain_password_first | |
          | change_password_plain_password_second | |
        And I press "change_password_save"
        Then the following fields should have an error:
              | change_password_plain_password_first |
        # valid new password
        When I fill in the following:
          | change_password_current_password | Abcd1234 |
          | change_password_plain_password_first | Abcd12345 |
          | change_password_plain_password_second | Abcd12345 |
        And I press "change_password_save"
        Then the form should be valid
        # restore old password (and assert the current password can be used as old password)
        When I click on "user-account, change-password"
        And I fill in the following:
          | change_password_current_password | Abcd12345 |
          | change_password_plain_password_first | Abcd1234 |
          | change_password_plain_password_second | Abcd1234 |
        And I press "change_password_save"
        Then the form should be valid
        And I should be on "/user-account/password-edit-done"

  @deputy
  Scenario: Change
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