Feature: Add PA users and activate PA user (journey)

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
    # password step
    And I activate the user with password "Abcd1234"
    When I click on "save"
    # assert pre-fill
    Then the following fields should have the corresponding values:
      | user_details_firstname | DEP1     |
      | user_details_lastname  | SURNAME1 |
    # check errors
    When I fill in the following:
      | user_details_firstname  |  |
      | user_details_lastname   |  |
      | user_details_jobTitle   |  |
      | user_details_phoneMain  |  |
      | user_details_paTeamName |  |
    And I press "user_details_save"
    Then the following fields should have an error:
      | user_details_firstname  |
      | user_details_lastname   |
      | user_details_jobTitle   |
      | user_details_phoneMain  |
    # correct
    When I fill in the following:
      | user_details_firstname  | Pubo           |
      | user_details_lastname   | Autoritus      |
      | user_details_jobTitle   | Solicitor      |
      | user_details_phoneMain  | 46745675674567 |
      | user_details_paTeamName | TEAM1          |
    And I press "user_details_save"
    Then the form should be valid
    # check I'm in the dashboard
    And I should see the "client-1000010" region
