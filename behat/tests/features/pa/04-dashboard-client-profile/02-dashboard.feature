Feature: PA dashboard

  Scenario: PA dashboard check visibility, pagination and search
    And I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
    # check pagination
    And I should see the "client" region exactly 15 times
    When I click on "paginator-page-2"
    Then I should see the "client" region exactly 15 times
    # check search
    When I fill in "search" with "02100010"
    And I press "search_submit"
    Then I should see the "client-02100010" region
    And I should see the "client" region exactly 1 times
    # check tabs
    When I click on "tab-ready"
    Then I should not see the "client" region
    # check navigation links
    When I click on "org-dashboard" in the navbar region
    Then the URL should match "/org"

  Scenario: PA links in header
    Given I am logged in as "behat-pa1@publicguardian.gov.uk" with password "Abcd1234"
    #PA links
    Then I should see the "org-dashboard" link
    And I should see the "org-settings" link
    And I should see the "logout" link
    #Lay deputy links
    And I should not see the "user-account" link
    And I should not see the "reports" link
