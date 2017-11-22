Feature: admin / AD

  @ad
  Scenario: Create AD user
    Given I load the application status from "admin-init"
    And emails are sent from "admin" area
    And I reset the email log
    And I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I create a new "ODR-disabled" "AD" user "Assis" "Ter" with email "behat-ad@publicguardian.gsi.gov.uk" and postcode "HA3"
    Then I should see "behat-ad@publicguardian.gsi.gov.uk" in the "users" region
    Then the response status code should be 200
    And the last email containing a link matching "/user/activate/" should have been sent to "behat-ad@publicguardian.gsi.gov.uk"

#  @ad
#  Scenario: activate AD user
#    Given emails are sent from "admin" area
#    When I activate the user with password "Abcd1234"
#
#
#      # user details page
#    Then the following fields should have the corresponding values:
#      | user_details_firstname | Assis |
#      | user_details_lastname  | Ter   |
#    And I press "user_details_save"
#    Then I should be on "/ad/"
#
#  @ad
#  Scenario: AD homepage
#    Given I am logged in to admin as "behat-ad@publicguardian.gsi.gov.uk" with password "Abcd1234"
#    Then I should be on "/ad/"
