Feature: admin / case manager

  Scenario: Create CM user
    Given emails are sent from "admin" area
    And I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I create a new "NDR-disabled" "case manager" user "Casero" "Managera" with email "behat-cm@publicguardian.gov.uk" and postcode "HA3"
    Then I should see "behat-cm@publicguardian.gov.uk" in the "users" region
    Then the response status code should be 200
    And the last email containing a link matching "/user/activate/" should have been sent to "behat-cm@publicguardian.gov.uk"

  Scenario: activate CM user
    Given emails are sent from "admin" area
    When I activate the user with password "Abcd1234" - no T&C expected
      # user details page
    Then the following fields should have the corresponding values:
      | user_details_firstname | Casero   |
      | user_details_lastname  | Managera |
    And I press "user_details_save"
    Then I should be on "/admin/client/search"


  Scenario: CM user can access self-user functionalities and client search
    Given I am logged in to admin as "behat-cm@publicguardian.gov.uk" with password "Abcd1234"
    # assert client search and client page return 200
    When I should be on "/admin/client/search"
    Then the response status code should be 200
    When I click on "client-detail-102-4"
    Then the response status code should be 200
    # assert user and password edit work
    When I click on "user-account, profile-show, profile-edit, save"
    Then I should see the "alert-message" region
    When I click on "user-account, password-edit"
    Then the response status code should be 200
    # assert other admin homepages are not accessible
    Then the following "admin" pages should return the following status:
      | /admin                               | 403 |
      | /admin/casrec-upload                 | 403 |
      | /admin/documents/list                | 403 |
      | /admin/settings/service-notification | 403 |
      | /ad                                  | 403 |


