Feature: admin / AD

  @ad
  Scenario: Create AD user
    Given I load the application status from "admin-init"
    And emails are sent from "admin" area
    And I reset the email log
    And I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I create a new "NDR-disabled" "AD" user "Assis" "Ter" with email "behat-ad@publicguardian.gov.uk" and postcode "HA3"
    Then I should see "behat-ad@publicguardian.gov.uk" in the "users" region
    Then the response status code should be 200
    And the last email containing a link matching "/user/activate/" should have been sent to "behat-ad@publicguardian.gov.uk"

  @ad
  Scenario: activate AD user
    Given emails are sent from "admin" area
    When I activate the user with password "Abcd1234" - no T&C expected
      # user details page
    Then the following fields should have the corresponding values:
      | user_details_firstname | Assis |
      | user_details_lastname  | Ter   |
    And I press "user_details_save"
    Then I should be on "/ad/"

  @ad
  Scenario: Add new assisted Lay and login on behalf
    Given I am logged in to admin as "behat-ad@publicguardian.gov.uk" with password "Abcd1234"
    Then I should be on "/ad/"
    # add assisted Lay
    When I press "ad_save"
    Then the following fields should have an error:
      | ad_firstname  |
      | ad_lastname   |
    When I fill in the following:
      | ad_firstname  | LayAssisted firstname |
      | ad_lastname   | LayAssisted lastname |
    And I press "ad_save"
    Then I should see "LayAssisted firstname" in the "users" region
    # view user
    When I click on "view" in the "users" region
    Then the response status code should be 200
    And I click on "back"
    # login as AD user ad check page loads OK
    When I click on "login-as"
    Then the response status code should be 200
    When I go to "user/details"
    Then the URL should match "user/details"
    And I should be in the "deputy" area

  @ad
  Scenario: Login on behalf of a newly created (not activated) Lay deputy
    Given I am logged in to admin as "behat-ad@publicguardian.gov.uk" with password "Abcd1234"
    # behat-lay-assisted@publicguardian.gov.uk
    # find user
    And I go to admin page "/admin"
    And I create a new "NDR-disabled" "Lay Deputy" user "Assis" "Ted" with email "behat-lay-assisted@publicguardian.gov.uk" and postcode "HA4"
    And I click on "view" in the "user-behat-lay-assistedpublicguardiangovuk" region
    # login on behalf
    And I click on "login-as"
    Then the response status code should be 200
    When I go to "user/details"
    Then the URL should match "user/details"
    And I should be in the "deputy" area


