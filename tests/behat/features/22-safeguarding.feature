Feature: Safeguarding
    
    @safeguarding @deputy
    Scenario: Setup the reporting user
        Given I am logged in to admin as "ADMIN@PUBLICGUARDIAN.GSI.GOV.UK" with password "Abcd1234"
        Then I should see "admin@publicguardian.gsi.gov.uk" in the "users" region
        When I fill in the following:
            | admin_email | behat-safe@publicguardian.gsi.gov.uk | 
            | admin_firstname | Safe | 
            | admin_lastname | Smith | 
            | admin_roleId | 2 |
        And I click on "save"
        Then I should see "behat-safe@publicguardian.gsi.gov.uk" in the "users" region
        Then I should see "Safe Smith" in the "users" region
        Given I am on "/logout"
        When I open the "/user/activate/" link from the email
        Then the response status code should be 200
        When I fill in the following: 
            | set_password_password_first   | Abcd1234 |
            | set_password_password_second  | Abcd1234 |
        And I press "set_password_save"
        Then the form should be valid
        #Then I should be on "user/details"
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
        Then the form should be valid
        When I fill in the following:
            | client_firstname | Peter |
            | client_lastname | White |
            | client_caseNumber | 123456ABC |
            | client_courtDate_day | 1 |
            | client_courtDate_month | 1 |
            | client_courtDate_year | 2014 |
            | client_allowedCourtOrderTypes_0 | 2 |
            | client_address |  1 South Parade |
            | client_address2 | First Floor  |
            | client_county | Nottingham  |
            | client_postcode | NG1 2HT  |
            | client_country | GB |
            | client_phone | 0123456789  |
        And I press "client_save"
        Then the form should be valid
        When I fill in the following:
            | report_endDate_day | 1 |
            | report_endDate_month | 1 |
            | report_endDate_year | 2015 |
        And I press "report_save"
        Then the form should be valid
        # assert you are on dashboard
        And the URL should match "report/\d+/overview"
        Then I save the application status into "safeuser"
    
    @safeguarding @deputy    
    Scenario: Lives with client
        When I load the application status from "reportuser"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I press "safeguarding_save"
        Then the form should be valid
        Then I follow "tab-safeguarding"
        Then the checkbox "safeguarding_doYouLiveWithClient_0" should be checked
    
    @safeguarding @deputy
    Scenario: Does not live with client
        When I load the application status from "reportuser"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_howOftenDoYouVisit_0 | everyday |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_0 | everyday |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_0 | everyday |
            | safeguarding_howOftenDoesClientSeeOtherPeople_0 | everyday |
            | safeguarding_anythingElseToTell | nothing to report |
        And I press "safeguarding_save"
        Then the form should be valid
        Then I follow "tab-safeguarding"
        Then the checkbox "safeguarding_doYouLiveWithClient_1" should be checked
        Then the checkbox "safeguarding_howOftenDoYouVisit_0" should be checked
        Then the checkbox "safeguarding_howOftenDoYouPhoneOrVideoCall_0" should be checked
        Then the checkbox "safeguarding_howOftenDoYouWriteEmailOrLetter_0" should be checked
        Then the checkbox "safeguarding_howOftenDoesClientSeeOtherPeople_0" should be checked
        And the "safeguarding_anythingElseToTell" field should contain "nothing to report"

    @safeguarding @deputy
    Scenario: User must answer sub questions when not living with client
        When I load the application status from "reportuser"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_1 | no |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I press "safeguarding_save"
        Then the form should be invalid
        And the following fields should have an error:
            | safeguarding_howOftenDoYouVisit_0 |
            | safeguarding_howOftenDoYouVisit_1 |
            | safeguarding_howOftenDoYouVisit_2 |
            | safeguarding_howOftenDoYouVisit_3 |
            | safeguarding_howOftenDoYouVisit_4 |
            | safeguarding_howOftenDoYouVisit_5 |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_0 |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_1 |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_2 |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_3 |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_4 |
            | safeguarding_howOftenDoYouPhoneOrVideoCall_5 |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_0 |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_1 |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_2 |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_3 |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_4 |
            | safeguarding_howOftenDoYouWriteEmailOrLetter_5 |
            | safeguarding_howOftenDoesClientSeeOtherPeople_0 |
            | safeguarding_howOftenDoesClientSeeOtherPeople_1 |
            | safeguarding_howOftenDoesClientSeeOtherPeople_2 |
            | safeguarding_howOftenDoesClientSeeOtherPeople_3 |
            | safeguarding_howOftenDoesClientSeeOtherPeople_4 |
            | safeguarding_howOftenDoesClientSeeOtherPeople_5 |
    
    @safeguarding @deputy    
    Scenario: Client does not receive care
        When I load the application status from "reportuser"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I press "safeguarding_save"
        Then the form should be valid
        Then I follow "tab-safeguarding"
        Then the checkbox "safeguarding_doesClientReceivePaidCare_1" should be checked
    
    @safeguarding @deputy
    Scenario: Client does receive care
        When I load the application status from "reportuser"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_0 | yes |
            | safeguarding_howIsCareFunded_0 | client_pays_for_all |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I press "safeguarding_save"
        Then the form should be valid
        Then I follow "tab-safeguarding"
        Then the checkbox "safeguarding_doesClientReceivePaidCare_0" should be checked
        And the checkbox "safeguarding_howIsCareFunded_0" should be checked
    
    @safeguarding @deputy
    Scenario: User must answer sub questions when receiving care
        When I load the application status from "reportuser"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_0 | yes |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I press "safeguarding_save"
        Then the form should be invalid
        And the following fields should have an error:
            | safeguarding_howIsCareFunded_0 |
            | safeguarding_howIsCareFunded_1 |
            | safeguarding_howIsCareFunded_2 |
              
    @safeguarding @deputy
    Scenario: Client has care plan
        When I load the application status from "reportuser"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_0 | yes |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
            | safeguarding_whenWasCarePlanLastReviewed_day | 1 |
            | safeguarding_whenWasCarePlanLastReviewed_month | 1 |
            | safeguarding_whenWasCarePlanLastReviewed_year | 2015 |
        And I press "safeguarding_save"
        Then the form should be valid
        Then I follow "tab-safeguarding"
        Then the checkbox "safeguarding_doesClientHaveACarePlan_0" should be checked
        And the "safeguarding_whenWasCarePlanLastReviewed_day" field should contain "01"
        And the "safeguarding_whenWasCarePlanLastReviewed_month" field should contain "01"
        And the "safeguarding_whenWasCarePlanLastReviewed_year" field should contain "2015"
        
    @safeguarding @deputy
    Scenario: Client does not have care plan
        When I load the application status from "reportuser"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I press "safeguarding_save"
        Then the form should be valid
        Then I follow "tab-safeguarding"
        Then the checkbox "safeguarding_doesClientHaveACarePlan_1" should be checked

    @safeguarding @deputy      
    Scenario: Who is doing the caring?
        When I load the application status from "reportuser"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "tab-safeguarding"
        And I fill in the following:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I press "safeguarding_save"
        Then the form should be valid
        Then I follow "tab-safeguarding"
        And the "safeguarding_whoIsDoingTheCaring" field should contain "Fred Jones" 
        
            
    Scenario: Client must answer sub questions when there is a care plan
    
    Scenario: Deputy must answer top level questions
    