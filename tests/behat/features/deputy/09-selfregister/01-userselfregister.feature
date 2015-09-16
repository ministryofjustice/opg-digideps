Feature: User Self Registration
    
    @deputy @wip
    Scenario: A user can enter their self registration information
        Given I am on "/register"
        And I fill in the following:
            | self_registration_firstname | Zac                |
            | self_registration_lastname  | Tolley             |
            | self_registration_email     | zac@thetolleys.com |
            | self_registration_clientLastname | Cross-Tolley  |
            | self_registration_caseNumber     | 12341234      |
        And I press "self_registration_save"
        Then I should see "Please check your email"
        And I should see "We've sent you a link to zac@thetolleys.com"
        And There should be a lay deputy account with id "zac@thetolleys.com" awaiting activation
        
