Feature: deputy / login and logout functionalities
    @infra
    Scenario: manual login
      Given I am logged in as "behat-lay-deputy-103@publicguardian.gov.uk" with password "Abcd1234"
      Then the URL should match "report/create/\d+"

    @deputy
    Scenario: manual logout
      Given I am logged in as "behat-lay-deputy-103@publicguardian.gov.uk" with password "Abcd1234"
      When I click on "logout"
      Then I should be on "/login"
      And I should see the "manual-logout-message" region

    @deputy
    Scenario: no cache
      Given I am logged in as "behat-lay-deputy-103@publicguardian.gov.uk" with password "Abcd1234"
      And I go to the homepage
      And I click on "user-account"
      Then the response should have the "Cache-Control" header containing "no-cache"
      Then the response should have the "Cache-Control" header containing "no-store"
      Then the response should have the "Cache-Control" header containing "must-revalidate"
      Then the response should have the "Pragma" header containing "no-cache"
