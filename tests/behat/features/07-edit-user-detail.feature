Feature: edit user details

    @deputy
    Scenario: edit user details
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I should be on "client/show"
        And I click on "my-details"
        And I click on "edit-user-details"
        Then I should be on "user/edit-your-details#edit-your-details"
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
        Then the form should contain an error
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
        Then the form should not contain an error
        Then I should be on "user"
        Then I should see "Paul Jamie" in the "my-details" region
        And I should see "103 Petty France" in the "my-details" region
        And I should see "020 3334 3556" in the "my-details" region
        And I should see "020 1234 5679" in the "my-details" region
        And I should see "behat-user@publicguardian.gsi.gov.uk" in the "my-details" region
     
  
    @deputy   
    Scenario: change user password
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I should be on "client/show"
        And I click on "my-details"
        And I click on "edit-user-details"
        # wrong old password
        When I fill in "user_details_password_current_password" with "this.is.the.wrong.password"
        And I press "user_details_save"
        Then the following fields should have an error:
              | user_details_password_current_password |
        # invalid new password
        When I fill in the following:
          | user_details_password_current_password | Abcd1234 |
          | user_details_password_plain_password_first | 1 |
          | user_details_password_plain_password_second | 2 |
        And I press "user_details_save"
        Then the following fields should have an error:
              | user_details_password_plain_password_first |      
        # unmatching new passwords
        When I fill in the following:
          | user_details_password_current_password | Abcd1234 |
          | user_details_password_plain_password_first | Abcd1234 |
          | user_details_password_plain_password_second | Abcd12345 |
        And I press "user_details_save"
        Then the following fields should have an error:
              | user_details_password_plain_password_first |  
        # valid new password
        When I fill in the following:
          | user_details_password_current_password | Abcd1234 |
          | user_details_password_plain_password_first | Abcd12345 |
          | user_details_password_plain_password_second | Abcd12345 |
        And I press "user_details_save"
        Then the form should not contain any error
        And I should be on "/user"
        # restore old password (and assert the current password can be used as old password)
        When I click on "edit-user-details"
        And I fill in the following:
          | user_details_password_current_password | Abcd12345 |
          | user_details_password_plain_password_first | Abcd1234 |
          | user_details_password_plain_password_second | Abcd1234 |
        And I press "user_details_save"
        Then the form should not contain any error
        And I should be on "/user"
      
         