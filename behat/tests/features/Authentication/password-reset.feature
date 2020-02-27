Feature: Users can reset their password via self-service

    Scenario: Deputy can reset password
        Given I am on "/login"
        When I follow "Forgotten your password?"
        And I fill in "password_forgotten_email" with "behat-lay-deputy-102-4@publicguardian.gov.uk"
        And I press "Reset your password"
        Then the form should be valid
        When I open the password reset page for "behat-lay-deputy-102-4@publicguardian.gov.uk"
        And I fill in the following:
            | reset_password_password_first  | Abcd12345 |
            | reset_password_password_second | Abcd12345 |
        And I press "Save password"
        And I am logged in as "behat-lay-deputy-102-4@publicguardian.gov.uk" with password "Abcd12345"
        Then the form should be valid

    Scenario: Admin can reset password
        Given I am on admin page "/login"
        When I follow "Forgotten your password?"
        And I fill in "password_forgotten_email" with "casemanager@publicguardian.gov.uk"
        And I press "Reset your password"
        Then the form should be valid
        When I open the admin password reset page for "casemanager@publicguardian.gov.uk"
        And I fill in the following:
            | reset_password_password_first  | Abcd12345 |
            | reset_password_password_second | Abcd12345 |
        And I press "Save password"
        And I am logged in to admin as "casemanager@publicguardian.gov.uk" with password "Abcd12345"
        Then the form should be valid

    Scenario: Invalid emails are not accepted
        Given I am on "/login"
        When I follow "Forgotten your password?"
        And I fill in "password_forgotten_email" with "invalidemail"
        And I press "Reset your password"
        Then the form should be invalid

    Scenario: Non-existent emails are accepted
        Given I am on "/login"
        When I follow "Forgotten your password?"
        And I fill in "password_forgotten_email" with "incorrectemail@publicguardian.gov.uk"
        And I press "Reset your password"
        Then the form should be valid
