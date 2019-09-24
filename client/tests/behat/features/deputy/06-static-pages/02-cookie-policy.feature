Feature: Cookie Policy

    @deputy
    Scenario: I see a link in the bottom footer of the service on every page
        Given I am on "/login"
        When I press "Cookies" in the "footer" region
        Then I should be on "/cookies"

    @deputy
    Scenario: Can save my cookie settings
        Given I am on "/cookies"
        Then I should not have a cookie policy
        When I fill in "cookie_permissions_usage_0" with "1"
        And I press "Save changes"
        Then I should have a cookie policy with usage enabled
        When I fill in "cookie_permissions_usage_0" with "0"
        And I press "Save changes"
        Then I should have a cookie policy with usage disabled

    @deputy
    Scenario: When I visit the site for the first time I see a cookie banner
        Given I am on "/login"
        Then I should see the "cookie-banner" region
        When I press "Cookie settings" in the "cookie-banner" region
        Then I should be on "/cookies"

    @deputy
    Scenario: I don't see the cookie banner when I have a policy set
        Given I have a cookie policy set
        And I am on "/login"
        Then I should not see the "cookie-banner" region
