Feature: admin / AD

  @ad
  Scenario: Add new assisted Lay and login on behalf
    Given I am logged in to admin as "behat-ad@publicguardian.gov.uk" with password "DigidepsPass1234"
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
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And the following users exist:
      | ndr | deputyType | firstName | lastName | email | postCode | activated |
      | enabled | LAY | Assis | Ted | behat-lay-assisted@publicguardian.gov.uk | HA4 | false |
    Given I am logged in to admin as "behat-ad@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I am on admin page "/admin"
    And I click on "view" in the "user-behat-lay-assistedpublicguardiangovuk" region
    And I click on "login-as"
    Then the response status code should be 200
    When I go to "user/details"
    Then the URL should match "user/details"
    And I should be in the "deputy" area
