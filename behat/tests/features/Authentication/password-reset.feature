Feature: Users can reset their password via self-service

    Scenario: Set up organisation and admin
        Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
        And the following users exist:
            | ndr      | deputyType | firstName | lastName | email                          | postCode | activated |
            | disabled | LAY        | Enrique   | Remondet | enrique75@mail.example         | SW1H 9AJ | true      |
            | disabled | ADMIN      | Odell     | Fiecke   | o.fiecke@publicguardian.gov.uk | SW1H 9AJ | true      |

    Scenario: Deputy can reset password
        Given I am on "/login"
        When I follow "Forgotten your password?"
        And I fill in "password_forgotten_email" with "enrique75@mail.example"
        And I press "Reset your password"
        Then the form should be valid
        When I open the password reset page for "enrique75@mail.example"
        And I fill in the following:
            | reset_password_password_first  | Abcd12345 |
            | reset_password_password_second | Abcd12345 |
        And I press "Save password"
        Then I should be on "/login"
        And I should see "Sign in with your new password"
        When I am logged in as "enrique75@mail.example" with password "Abcd12345"
        Then the form should be valid
        Given I am on "/login"
        Then I should see "Sign in"
        And I should not see "Sign in with your new password"

    Scenario: Admin can reset password
        Given I am on admin page "/login"
        When I follow "Forgotten your password?"
        And I fill in "password_forgotten_email" with "o.fiecke@publicguardian.gov.uk"
        And I press "Reset your password"
        Then the form should be valid
        When I open the admin password reset page for "o.fiecke@publicguardian.gov.uk"
        And I fill in the following:
            | reset_password_password_first  | Abcd12345 |
            | reset_password_password_second | Abcd12345 |
        And I press "Save password"
        Then I should be on "/login"
        And I should see "Sign in with your new password"
        When I am logged in to admin as "o.fiecke@publicguardian.gov.uk" with password "Abcd12345"
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
