Feature: Formatted Report
    
    @formatted-report
    Scenario: Setup the reporting user
        Given I am on "/login"
        And I goto 
        When I fill in the following:
            | login_email     | ADMIN@PUBLICGUARDIAN.GSI.GOV.UK |
            | login_password  | Abcd1234 |
        And I click on "login"
        Then I should see "admin@publicguardian.gsi.gov.uk" in the "users" region
        When I fill in the following:
            | admin_email | behat-report@publicguardian.gsi.gov.uk | 
            | admin_firstname | John | 
            | admin_lastname | Doe | 
            | admin_roleId | 2 |
        Given I am on "/logout"
        When I open the "/user/activate/" link from the email
        Then the response status code should be 200
        Given I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I should be on "user/details"
        When I fill in the following:
            | user_details_firstname | John |
            | user_details_lastname | Doe |
            | user_details_address1 | 102 Petty France |
            | user_details_address2 | MOJ |
            | user_details_address3 | London |
            | user_details_addressPostcode | SW1H 9AJ |
            | user_details_addressCountry | GB |
            | user_details_phoneMain | 020 3334 3555  |
            | user_details_phoneAlternative | 020 1234 5678  |
        And I press "user_details_save"
        Then the form should not contain an error
        When I fill in the following:
            | client_firstname | Peter |
            | client_lastname | White |
            | client_caseNumber | 123456ABC |
            | client_courtDate_day | 1 |
            | client_courtDate_month | 1 |
            | client_courtDate_year | 2014 |
            | client_allowedCourtOrderTypes_1 | 1 |
            | client_address |  1 South Parade |
            | client_address2 | First Floor  |
            | client_county | Nottingham  |
            | client_postcode | NG1 2HT  |
            | client_country | GB |
            | client_phone | 0123456789  |
        And I press "client_save"
        Then the form should not contain an error
        When I fill in the following:
            | report_endDate_day | 1 |
            | report_endDate_month | 1 |
            | report_endDate_year | 2015 |
        And I press "report_save"
        Then the form should not contain an error
        # assert you are on dashboard
        And the URL should match "report/\d+/overview"
        Then I save the application status into "whatever"
        
    @formatted-report
    Scenario: A report lists decisions with and without client involvement
        When I load the application status from "whatever"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I goto "report/1/decisions"
        And I follow "tab-decisions"
        # Start by adding some decisions
        When I click on "add-a-decision"
        And I fill in the following:
            | decision_description | 3 beds |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 85% |
        Then I press "decision_save"
        And the form should not contain an error
        Then I click on "add-a-decision"
        # add another decision
        And I fill in the following:
            | decision_description | 3 beds |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 85% |
        Then I press "decision_save"
        And the form should not contain an error
        # Next, some contacts
        Then I follow "tab-contacts"
        And I click on "add-a-contact"
        And I fill in the following:
            | contact_contactName | Andy White |
            | contact_relationship | brother  |
            | contact_explanation | no explanation |
            | contact_address | 45 Noth Road |
            | contact_address2 | Inslington |
            | contact_county | London |
            | contact_postcode | N2 5JF |
            | contact_country | GB |
        And I press "contact_save"
        And the form should not contain an error


        And I add a dummy bank account
        And I add a dummy asset
        And I add a decision "blah blah" with client involvement saying "blah blah"
        And I add a decision "blah blah" with client involvement saying "blah blah"
        And I add a decision "blah blah" with no client involvement
        And I submit the report
        Then I view the formatted report
        Then the formatted report contains 3 decisions
    
  
    Scenario: A report lists decisions with and without client involvement
        Given the laydeputy@digital user is signed up
        And I login as laydeputy
        Then I add a dummy contact
        And I add a dummy bank account
        And I add a dummy asset
        And give a reason for no decision as "small budget"  
        And I submit the report
        Then I view the formatted report
        Then the formatted report contains 3 decisions 