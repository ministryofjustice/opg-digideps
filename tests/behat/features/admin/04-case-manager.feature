Feature: admin / case manager

  Scenario: Create CM user
    Given I load the application status from "admin-init"
    And emails are sent from "admin" area
    And I reset the email log
    And I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I create a new "NDR-disabled" "case manager" user "Casero" "Managera" with email "behat-cm@publicguardian.gsi.gov.uk" and postcode "HA3"
    Then I should see "behat-cm@publicguardian.gsi.gov.uk" in the "users" region
    Then the response status code should be 200
    And the last email containing a link matching "/user/activate/" should have been sent to "behat-cm@publicguardian.gsi.gov.uk"

  @cm
  Scenario: activate CM user
    Given emails are sent from "admin" area
    When I activate the user with password "Abcd1234" - no T&C expected
      # user details page
    Then the following fields should have the corresponding values:
      | user_details_firstname | Casero |
      | user_details_lastname  | Managera   |
    And I press "user_details_save"
    Then I should be on "/admin/client/search"


  @cm
  Scenario: CM user can access self-user functionalities and client search
    Given I am logged in to admin as "behat-cm@publicguardian.gsi.gov.uk" with password "Abcd1234"
    When I should be on "/admin/client/search"
    And the response status code should be 200
    When I go to admin page "/user/details"
    And the response status code should be 200
    #
    And the admin URL "/admin" should not be accessible
    # edit surname
    And I go to "/user/details"
    Then the response status code should be 200

