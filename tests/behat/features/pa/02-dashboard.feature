#Requires full import of csv data
@pa @pateam
Feature: PA dashboard

  Scenario: PA dashboard check visibility, pagination and search
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    # check pagination
    And I should see the "client" region exactly 15 times
    When I click on "paginator-page-2"
    Then I should see the "client" region exactly 2 times
    # check search
    When I fill in "search" with "1000010"
    And I press "search_submit"
    Then I should see the "client-1000010" region
    And I should see the "client" region exactly 1 times
    # check tabs
    When I click on "tab-ready"
    Then I should not see the "client" region
    # check navigation links
    When I click on "pa-dashboard" in the navbar region
    Then I should be on "/pa/"


