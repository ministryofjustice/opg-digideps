Feature: Contact details

  Scenario: Create users for scenarios
    Given I am logged in to admin as 'super-admin@publicguardian.gov.uk' with password 'Abcd1234'

    And the following users exist:
      | ndr      | deputyType | firstName | lastName | email                          | postCode | activated |
      | enabled  | LAY        | Lay       | User     | ndr1234@publicguardian.gov.uk  | SW1H 9AJ | true      |
      | disabled | LAY        | Lay       | User     | lay1234@publicguardian.gov.uk  | SW1H 9AJ | true      |
      | disabled | PA         | Pa        | User     | pa1234@publicguardian.gov.uk   | SW1H 9AJ | true      |
      | disabled | PROF       | Prof      | User     | prof1234@publicguardian.gov.uk | SW1H 9AJ | true      |

  Scenario: Home screen should show public helpline
    When I go to "/"
    Then I should see the "contact-details" region
    And I should see "0300 456 0300" in the "contact-details" region

  Scenario: Admin should not show any helpline
    Given I go to admin page "/"
    Then I should not see the "contact-details" region
    When I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    Then I should not see the "contact-details" region

  Scenario: NDR should see general helpline
    Given I am logged in as "ndr1234@publicguardian.gov.uk" with password "Abcd1234"
    Then I should see the "contact-details" region
    And I should see "0115 934 2700" in the "contact-details" region

  Scenario: Lay deputy should see general helpline
    Given I am logged in as "lay1234@publicguardian.gov.uk" with password "Abcd1234"
    Then I should see the "contact-details" region
    And I should see "0115 934 2700" in the "contact-details" region

  Scenario: Professional deputy should see professional helpline
    Given I am logged in as "prof1234@publicguardian.gov.uk" with password "Abcd1234"
    Then I should see the "contact-details" region
    And I should see "0115 934 2819" in the "contact-details" region

  Scenario: Public authority deputy should see professional helpline
    Given I am logged in as "pa1234@publicguardian.gov.uk" with password "Abcd1234"
    Then I should see the "contact-details" region
    And I should see "0115 934 2817" in the "contact-details" region

  Scenario: Cleanup users
    Given I am logged in to admin as 'super-admin@publicguardian.gov.uk' with password 'Abcd1234'
    And I delete the following users:
      | email                          |
      | ndr1234@publicguardian.gov.uk  |
      | lay1234@publicguardian.gov.uk  |
      | pa1234@publicguardian.gov.uk   |
      | prof1234@publicguardian.gov.uk |
