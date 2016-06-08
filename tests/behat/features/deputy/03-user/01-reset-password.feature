Feature: deputy / password reset
    
    @deputy
    Scenario: Password reset
      Given I load the application status from "report-submit-pre" 
      And I save the application status into "reset-password-start"
      And I check email sent from "deputy" area
      And I reset the email log
      And I go to "/logout"
      And I go to "/login"
      And I save the page as "forgotten-password-login"
      When I click on "forgotten-password"
      And I save the page as "forgotten-password"
      # empty form]
      And I fill in "password_forgotten_email" with ""
      And I press "password_forgotten_submit"
      Then the form should be invalid
      # invalid email
      When I fill in "password_forgotten_email" with "invalidemail"
      And I press "password_forgotten_submit"
      Then the form should be invalid
      # non-existing email (no email is sent)
      When I fill in "password_forgotten_email" with "ehat-not-existing@publicguardian.gsi.gov.uk"
      And I press "password_forgotten_submit"
      Then the form should be valid
      And I click on "return-to-login"
      And no email should have been sent
      # existing email (email is now sent)
      When I go to "/login" 
      And I click on "forgotten-password"
      And I fill in "password_forgotten_email" with "behat-user@publicguardian.gsi.gov.uk"
      And I press "password_forgotten_submit"
      Then the form should be valid
      And I save the page as "forgotten-password-sent"
      And I click on "return-to-login"
      And the last email should have been sent to "behat-user@publicguardian.gsi.gov.uk"
      # open password reset page
      When I open the "/user/password-reset/" link from the email
      And I save the page as "forgotten-password-reset"
      # empty
      When I fill in the following: 
          | reset_password_password_first   |  |
          | reset_password_password_second  |  |
      And I press "reset_password_save"
      Then the form should be invalid
      #password mismatch
      When I fill in the following: 
          | reset_password_password_first   | Abcd1234 |
          | reset_password_password_second  | Abcd12345 |
      And I press "reset_password_save"
      Then the form should be invalid
      # (nolowercase, nouppercase, no number skipped as already tested in "set password" scenario)
      # correct !!
      When I fill in the following: 
          | reset_password_password_first   | Abcd12345 |
          | reset_password_password_second  | Abcd12345 |
      And I press "reset_password_save"
      Then the form should be valid
      And the URL should match "report/\d+/overview"
      And I save the page as "forgotten-password-logged"
      # test login
      Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd12345"
      Then the URL should match "report/\d+/overview"
      # assert set password link is not accessible
      When I open the "/user/password-reset/" link from the email
      Then the response status code should be 500
      # restore previous password
      And I load the application status from "reset-password-start"
       
