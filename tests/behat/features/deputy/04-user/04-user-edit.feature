Feature: deputy / report / edit user

    @deputy
    Scenario: edit user details
        Given I load the application status from "report-submit-pre"
        And I am logged in as "behat-user@publicguardian.gov.uk" with password "Abcd1234"
        And I click on "user-account, profile-show, profile-edit"
        Then I should be on "/deputyship-details/your-details/edit"
        Then the following fields should have the corresponding values:
             | profile_firstname | John |
             | profile_lastname | Doe |
             | profile_address1 | 102 Petty France |
             | profile_address2 | MOJ |
             | profile_address3 | London |
             | profile_addressPostcode | P0ST C0D3 |
             | profile_addressCountry | GB |
             | profile_phoneMain | 020 3334 3555  |
             | profile_phoneAlternative | 020 1234 5678  |
        When I fill in the following:
            | profile_firstname |  |
            | profile_lastname |  |
            | profile_address1 | |
            | profile_addressPostcode | |
            | profile_addressCountry | |
            | profile_phoneMain |   |
        And I press "profile_save"
        Then the following fields should have an error:
            | profile_firstname |
            | profile_lastname |
            | profile_address1 |
            | profile_addressPostcode |
            | profile_addressCountry |
            | profile_phoneMain |
        And I press "profile_save"
        Then the form should be invalid
        When I fill in the following:
           | profile_firstname | Paul |
           | profile_lastname | Jamie |
           | profile_address1 | 103 Petty France |
           | profile_address2 | MOJDS |
           | profile_address3 | London |
           | profile_addressPostcode | SW1H 9AA |
           | profile_addressCountry | GB |
           | profile_phoneMain | 020 3334 3556  |
           | profile_phoneAlternative | 020 1234 5679  |
        And I press "profile_save"
        Then the form should be valid
        And I should be on "/deputyship-details/your-details"
        And I should see "Paul Jamie" in the "profile-name" region
        And I should see "SW1H 9AA" in the "profile-address" region

    # @deputy
    # Scenario: Notification email sent for lay deputy changes
    #     Given emails are sent from "deputy" area
    #     And I reset the email log
    #     And I am logged in as "laydeputy@publicguardian.gov.uk" with password "Abcd1234"
    #     And I click on "user-account, profile-show, profile-edit"
    #     When I fill in the following:
    #        | profile_firstname | Maruxita |
    #        | profile_lastname | Alvarez |
    #        | profile_address1 | 1 Scotland Street |
    #        | profile_address2 | Edinburgh |
    #        | profile_address3 | Midlothian |
    #        | profile_addressPostcode | EH1 1AA |
    #        | profile_addressCountry | NL |
    #        | profile_phoneMain | 0131 111 1111 |
    #        | profile_phoneAlternative | 0131 222 2222 |
    #     And I press "profile_save"
    #     Then I should be on "/deputyship-details/your-details"
    #     And the last email should have been sent to "digideps+update-contact@digital.justice.gov.uk"
    #     And the last email should contain "The contact details of the following deputy have been updated:"
    #     And the last email should contain "Maruxita Alvarez"
    #     And the last email should contain "1 Scotland Street"
    #     And the last email should contain "Netherlands"
    #     And the last email should contain "0131 111 1111"

    @deputy
    Scenario: change user password
        Given I am logged in as "behat-user@publicguardian.gov.uk" with password "Abcd1234"
        And I click on "user-account, password-edit"
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
        When I click on "user-account, password-edit"
        And I fill in the following:
          | change_password_current_password | Abcd12345 |
          | change_password_plain_password_first | Abcd1234 |
          | change_password_plain_password_second | Abcd1234 |
        And I press "change_password_save"
        Then the form should be valid
        And I should be on "/deputyship-details/your-details/change-password/done"
