Feature: User Self Registration
    
    @deputy @wip
    Scenario: A user can enter their self registration information
        Given I load the application status from "init" 
        And I truncate the users from CASREC:
        And I reset the email log
        #
        # Add the user and expect failure (not matching in CASREC)
        #
        And I am on "/register"
        And I fill in the following:
            | self_registration_firstname | Zac                |
            | self_registration_lastname  | Tolley             |
            | self_registration_email     | behat-zac.tolley@digital.justice.gov.uk |
            | self_registration_postcode  | WRONG! |
            | self_registration_clientLastname | Cross-Tolley  |
            | self_registration_caseNumber     | 12341234      |
        And I press "self_registration_save"
        Then I should see a "#error-heading" element
        And I should be on "/register"
        #
        # Add user to casrec and expect error for postcode
        # 
        Given I add the following users to CASREC:
            | Case      | Surname       | Deputy No | Dep Surname  | Dep Postcode | 
            |12341234   | Cross-Tolley  | D001      | Tolley | SW1 3RF      |
        #
        # expect postcode error
        #
        And I press "self_registration_save"
        Then the following fields should have an error:
            | self_registration_postcode |
        #
        # fix postcode and expect success
        #
        And I fill in the following:
            | self_registration_postcode  | SW1 3RF |  
        And I press "self_registration_save"
        Then the form should be valid
        Then I should see "Please check your email"
        And I should see "We've sent you a link to behat-zac.tolley@digital.justice.gov.uk"
        And the last email containing a link matching "/user/activate/" should have been sent to "behat-zac.tolley@digital.justice.gov.uk"
        When I open the "/user/activate/" link from the email
        Then the response status code should be 200
        #
        # check user is created
        #
        Then I am on admin login page
        And I fill in the following:
            | login_email     | admin@publicguardian.gsi.gov.uk |
            | login_password  | Abcd1234 |
        Then I click on "login"
        Then I should see "behat-zac.tolley@digital.justice.gov.uk" in the "users" region
        

    @deputy @wip
    Scenario: Inform the use that email already exists
        Given I am on "/register"
        And I fill in the following:
            | self_registration_firstname | Zac                |
            | self_registration_lastname  | Tolley             |
            | self_registration_email     | behat-zac.tolley-dup@digital.justice.gov.uk |
            | self_registration_postcode  | SW1 3RF |
            | self_registration_clientLastname | Cross-Tolley  |
            | self_registration_caseNumber     | 12341234      |
        And I press "self_registration_save"
        Then I should see "Please check your email"
        Given I am on "/register"
        And I fill in the following:
            | self_registration_firstname | Zac                |
            | self_registration_lastname  | Tolley             |
            | self_registration_email     | behat-zac.tolley-dup@digital.justice.gov.uk |
            | self_registration_postcode  | SW1 3RF |
            | self_registration_clientLastname | Cross-Tolley  |
            | self_registration_caseNumber     | 12341234      |
        And I press "self_registration_save"
        Then the following fields should have an error:
            | self_registration_email |
