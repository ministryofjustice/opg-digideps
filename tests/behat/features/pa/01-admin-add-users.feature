Feature: admin / upload PA users

  Scenario: add PA users
    Given emails are sent from "admin" area
    And I reset the email log
    And I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
      # upload PA users
    When I click on "admin-upload-pa"
    When I attach the file "behat-pa.csv" to "admin_upload_file"
    And I press "admin_upload_upload"
    Then the form should be valid
    #Then I should see "Added 1 PA users"
      # activate PA user 1
    When I click on "admin-homepage"
    And I click on "send-activation-email" in the "user-behat-pa1publicguardiangsigovuk" region
    Then the response status code should be 200
    And the last email containing a link matching "/user/activate/" should have been sent to "behat-pa1@publicguardian.gsi.gov.uk"


  Scenario: PA user registration steps
    Given emails are sent from "admin" area
    When I open the "/user/activate/" link from the email
    And I activate the user with password "Abcd1234"
    And I set the user details to:
      | name    | Pubo             | Autoritus      |        |     |    |
      | address | 102 Petty France | MOJ            | London | HA2 | GB |
      | phone   | 46745675674567   | 46745675674567 |        |     |    |
    Then the URL should match "/pa"
    And I should see the "client-1000010" region


