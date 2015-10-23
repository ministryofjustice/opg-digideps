Feature: User Self Registration
    
    @deputy @wip
    Scenario: A user can enter their self registration information
        Given I load the application status from "init" 
        And I reset the email log
        And I am on "/register"
        And I fill in the following:
            | self_registration_firstname | Zac                |
            | self_registration_lastname  | Tolley             |
            | self_registration_email     | behat-zac.tolley@digital.justice.gov.uk |
            | self_registration_postcode  | SW1 3RF |
            | self_registration_clientLastname | Cross-Tolley  |
            | self_registration_caseNumber     | 12341234      |
        And I press "self_registration_save"
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

    @deputy @wip
    Scenario: A user can self register and activate
        Given I load the application status from "init"
        And I reset the email log
        And I am on "/register"
        And I fill in the following:
            | self_registration_firstname | Zac                |
            | self_registration_lastname  | Tolley             |
            | self_registration_email     | behat-zac.tolley@digital.justice.gov.uk |
            | self_registration_postcode  | SW1 3RF |
            | self_registration_clientLastname | Cross-Tolley  |
            | self_registration_caseNumber     | 12341234      |
        And I press "self_registration_save"
        Then I should see "Please check your email"
        And I should see "We've sent you a link to behat-zac.tolley@digital.justice.gov.uk"
        And the last email containing a link matching "/user/activate/" should have been sent to "behat-zac.tolley@digital.justice.gov.uk"
        When I open the "/user/activate/" link from the email
        Then the response status code should be 200
        When I fill in the following:
            | set_password_password_first   | Abcd1234 |
            | set_password_password_second  | Abcd1234 |
        And I press "set_password_save"
        Then the response status code should be 200
        Then the URL should match "/user/details"
        When I fill in the following:
            | user_details_address1 | Address1 |
            | user_details_addressCountry | GB |
            | user_details_phoneMain | 0777 222 333 |
        And I press "user_details_save"
        Then the response status code should be 200
        Then the URL should match "/client/add"
        Then I fill in the following:
            | client_firstname | Fred |
            | client_courtDate_day | 01 |
            | client_courtDate_month | 01 |
            | client_courtDate_year | 2014 |
            | client_address |  addres1 |
            | client_county |  Berks |
            | client_country | GB    |
            | client_postcode | SW1 1RH |
            | client_phone | 0777 123 1234 |
            | client_allowedCourtOrderTypes_1 | 1 |
        And I press "client_save"
        Then the URL should match "/report/create/\d+"
        And I fill in the following:
            | report_endDate_day | 1 |
            | report_endDate_month | 1 |
            | report_endDate_year | 2015 |
        Then I press "report_save"
        Then the URL should match "/report/\d+/overview"
