Feature: Helpline

  @deputy
  Scenario: Home screen should show public helpline
    When I go to "/"
    Then I should see the "helpline" region
    And I should see "0300 456 0300" in the "helpline" region

  @deputy
  Scenario: Admin should not show any helpline
    Given I go to admin page "/"
    Then I should not see the "helpline" region
    When I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    Then I should not see the "helpline" region

  @deputy
  Scenario: NDR should see general helpline
    Given I am logged in as "behat-lay-deputy-ndr@publicguardian.gov.uk" with password "Abcd1234"
    Then I should see the "helpline" region
    And I should see "0115 934 2700" in the "helpline" region

  @deputy
  Scenario: Lay deputy should see general helpline
    Given I am logged in as "behat-lay-deputy-103-4@publicguardian.gov.uk" with password "Abcd1234"
    Then I should see the "helpline" region
    And I should see "0115 934 2700" in the "helpline" region

  @deputy
  Scenario: Professional deputy should see professional helpline
    Given I am logged in as "behat-prof-deputy-102-5@publicguardian.gov.uk" with password "Abcd1234"
    Then I should see the "helpline" region
    And I should see "0115 934 2819" in the "helpline" region

  @deputy
  Scenario: Public authority deputy should see professional helpline
    Given I am logged in as "behat-pa-deputy-102-6@publicguardian.gov.uk" with password "Abcd1234"
    Then I should see the "helpline" region
    And I should see "0115 934 2817" in the "helpline" region
