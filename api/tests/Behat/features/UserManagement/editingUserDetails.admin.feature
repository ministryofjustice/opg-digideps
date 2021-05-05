Feature: Editing Deputy details as an admin user
  In order to ensure that deputy information can be updated internally
  As an admin user
  I need to view and edit details about each deputy

  Scenario: Create user for editing
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And the following users exist:
      | ndr      | deputyType | firstName | lastName    | email                   | postCode | activated |
      | disabled | LAY        | Jenny     | Ferguson    | jenny.ferguson@test.com | HA4      | true      |
      | disabled | LAY        | Tom       | Aikens      | tom.aikens@test.com     | HA9      | true      |

  Scenario: Super admin user edits a deputy
    Given I am logged in to admin as "super-admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "user-jennyfergusontestcom"
    Then the following fields should have the corresponding values:
      | admin_firstname       | Jenny                   |
      | admin_lastname        | Ferguson                |
      | admin_email           | jenny.ferguson@test.com |
      | admin_addressPostcode | HA4                     |
    When I fill in the following:
      | admin_firstname       | |
      | admin_lastname        | |
      | admin_email           | |
      | admin_addressPostcode | |
    And I press "admin_save"
    Then the form should be invalid
    And the following fields should have an error:
      | admin_firstname       |
      | admin_lastname        |
      | admin_email           |
      | admin_addressPostcode |
    When I fill in the following:
      | admin_firstname       | Paula                |
      | admin_lastname        | Jones                |
      | admin_email           | paula.jones@test.com |
      | admin_addressPostcode | LN3                  |
    And I press "admin_save"
    Then the form should be valid
    And the following fields should have the corresponding values:
      | admin_firstname       | Paula                |
      | admin_lastname        | Jones                |
      | admin_email           | paula.jones@test.com |
      | admin_addressPostcode | LN3                  |

  Scenario: Regular admin user edits a deputy
    Given I am logged in to admin as "super-admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    And I click on "user-tomaikenstestcom"
    Then I should not see "tom.aikens@test.com"
    And the following fields should have the corresponding values:
      | admin_firstname       | Tom    |
      | admin_lastname        | Aikens |
      | admin_addressPostcode | HA9    |
    When I fill in the following:
      | admin_firstname       | |
      | admin_lastname        | |
      | admin_addressPostcode | |
    And I press "admin_save"
    Then the form should be invalid
    And the following fields should have an error:
      | admin_firstname       |
      | admin_lastname        |
      | admin_addressPostcode |
    When I fill in the following:
      | admin_firstname       | Paula                |
      | admin_lastname        | Jones                |
      | admin_addressPostcode | LN3                  |
    And I press "admin_save"
    Then the form should be valid
    And the following fields should have the corresponding values:
      | admin_firstname       | Paula                |
      | admin_lastname        | Jones                |
      | admin_addressPostcode | LN3                  |
