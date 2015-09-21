Feature: User Self Registration
    
    @deputy @wip
    Scenario: A user can enter their self registration information
        Given I am on "/register"
        And I fill in the following:
            | self_registration_firstname | Zac                |
            | self_registration_lastname  | Tolley             |
            | self_registration_email     | behat-zac.tolley@digital.justice.gov.uk |
            | self_registration_clientLastname | Cross-Tolley  |
            | self_registration_caseNumber     | 12341234      |
        And I press "self_registration_save"
        Then I should see "Please check your email"
        And I should see "We've sent you a link to behat-zac.tolley@digital.justice.gov.uk"
        Then I am on admin login page
        And I fill in the following:
            | login_email     | admin@publicguardian.gsi.gov.uk |
            | login_password  | Abcd1234 |
        Then I click on "login"
        Then I should see "behat-zac.tolley@digital.justice.gov.uk" in the "users" region

    @deputy @wip
    Scenario: Inform the use that email already exists
        And I fill in the following:
            | self_registration_firstname | Zac                |
            | self_registration_lastname  | Tolley             |
            | self_registration_email     | behat-zac.tolley@digital.justice.gov.uk |
            | self_registration_clientLastname | Cross-Tolley  |
            | self_registration_caseNumber     | 12341234      |
        And I press "self_registration_save"
        Then I should see "Please check your email"
        Given I am on "/register"
        And I fill in the following:
            | self_registration_firstname | Zac                |
            | self_registration_lastname  | Tolley             |
            | self_registration_email     | behat-zac.tolley@digital.justice.gov.uk |
            | self_registration_clientLastname | Cross-Tolley  |
            | self_registration_caseNumber     | 12341234      |
        And I press "self_registration_save"
        Then the following fields should have an error:
            | email |
        
