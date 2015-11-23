Feature: deputy / report / submit
    
    @deputy
    Scenario: report declaration page
        Given I set the report 1 end date to 3 days ago
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "client-home"
        Then I should not see the "download-2015-report" link
        When I click on "report-2015"    
        And I confirm the report is ready to be submitted
        And I follow "edit-report_add_further_info"
        Then I press "report_add_info_saveAndContinue"
        Then the URL should match "/report/\d+/declaration"
        And I save the page as "report-submit-declaration"
        
    @deputy
    Scenario: report submission
        Given I reset the email log
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I save the application status into "report-submit-pre"
        # assert after login I'm redirected to report page
        Then the URL should match "/report/\d+/overview"
        # assert I cannot access the submitted page directly
        And the URL "/report/1/submitted" should not be accessible
        # assert I cannot access the submit page from declaration page
        When I go to "/report/1/declaration"
        Then the URL "/report/1/submitted" should not be accessible
        And I go to the "2015" report overview page
        # submit without ticking "agree"
        When I go to "/report/1/declaration"
        And I press "report_declaration_save"
        # tick agree and submit
        When I check "report_declaration_agree"
        When I fill in the following:
            | report_declaration_allAgreed_0 | 1 |
        And I press "report_declaration_save"
        And the form should be valid
        And the response status code should be 200
        And the URL should match "/report/\d+/submitted"
        And I save the page as "report-submit-submitted"
        # assert report display page is not broken
        When I go to "/report/1/display"
        Then the response status code should be 200
        And I save the page as "report-submit-display"
        # assert email has been sent/wrote into the disk
        And the last email containing a link matching "/report/[0-9]+/overview" should have been sent to "behat-user@publicguardian.gsi.gov.uk"
        # assert confirmation email has been sent
        And the second_last email should have been sent to "behat-digideps@digital.justice.gov.uk"
        And the second_last email "application/xml" part should contain the following:
            | caseNumber | 12345ABC |
            | ClientLastName | White |
#            | moneyInTotal |  3,250.00 |
#            | moneyOutTotal | 7,500.50 |
            | assetsTotal | 263,000.00 |
            | statusString | Deputy agreed |
            | statusDeputyName | John Doe |
        And I save the application status into "report-submit-post"
    

    @deputy
    Scenario: submit feedback after report
        Given I reset the email log
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I go to "/report/1/submitted"
        And I press "feedback_report_save"
        Then the form should be invalid
        And fill in "feedback_report_satisfactionLevel_2" with "Neither satisfied or dissatisfied"
        And I press "feedback_report_save"
        Then the form should be valid
        And I should be on "/report/1/submit_feedback"
        And the last email should contain "Neither satisfied or dissatisfied"


    @deputy
    Scenario: assert 2nd year report has been created
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "client-home"
        And I edit lastest active report
        When I click on "client-home"
        And I click on "report-2015-to-2016"
        And I save the page as "report-property-affairs-homepage"
        Then I should see a "#edit-contacts" element
        And I should see a "#edit-decisions" element
        And I should see a "#edit-accounts" element
        And I should see a "#edit-assets" element
#        When I follow "edit-accounts"
#        And I click on "account-1234"
#        # check no data was previously saved
#        Then the following fields should have the corresponding values:
#            | transactions_moneyIn_0_amount        |  |
#            | transactions_moneyIn_15_amount       |  |
#            | transactions_moneyIn_15_moreDetails  |  |
#            | transactions_moneyOut_0_amount       |  |
#            | transactions_moneyOut_11_amount      |  |
#            | transactions_moneyOut_11_moreDetails |  |
#        And I save the page as "report-account-transactions-empty"
#        #check account details
#        And I click on "edit-account-details"
#        Then the following fields should have the corresponding values:
#            | account_bank    | HSBC main account |
#            | account_accountNumber | 1234 |
#            | account_sortCode_sort_code_part_1 | 12 |
#            | account_sortCode_sort_code_part_2 | 34 |
#            | account_sortCode_sort_code_part_3 | 56 |
#            | account_openingBalance  | -3,000.50 |
        

    @deputy
    Scenario: assert report is not editable after submission
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I click on "client-home"
        # assert I'm on the client homepage (cannot redirect to report overview as not acessible anymore)
        Then I should be on "/client/show"
        Then I should not see the "edit-report-period-2015-report" link
        And I should not see the "report-2015" link
        And I should see the "report-2015-submitted-on" region
        And the URL "/report/1/overview" should not be accessible
        And the URL "/report/1/contacts" should not be accessible
        And the URL "/report/1/decisions" should not be accessible
        And the URL "/report/1/accounts" should not be accessible
        And the URL "/report/1/account/1" should not be accessible
        And the URL "/report/1/assets" should not be accessible
        
    @deputy
    Scenario: report download
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I click on "client-home"
        # download report from confirmation page
        When I go to "/report/1/submitted"
        When I click on "download-report"
        And the response should contain "12345ABC"
        And the response should contain "Peter White"
        # download report from client page
        #When I go to the homepage
        When I click on "client-home"
        And I click on "download-2015-report"
        And the response should contain "12345ABC"
        And the response should contain "Peter White"
        # test go back link
        When I click on "back-to-client"
        Then I should be on "/client/show"


    @deputy
    Scenario: change report to "not submitted" 
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I change the report "1" submitted to "false"


    @deputy @wip
    Scenario: Must agree
        Given I reset the email log
        When I load the application status from "report-submit-pre"
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I go to "/report/1/declaration"
        When I fill in the following:
            | report_declaration_allAgreed_0 | 1 |
        And I press "report_declaration_save"
        Then the following fields should have an error:
            | report_declaration_agree |
        Then I check "report_declaration_agree"
        When I fill in the following:
            | report_declaration_allAgreed_0 | 1 |
        And I press "report_declaration_save"
        Then the URL should match "/report/\d+/submitted"
        Then I load the application status from "report-submit-post"

        
    @deputy @wip
    Scenario: Must all agree
        Given I reset the email log
        When I load the application status from "report-submit-pre"
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I go to "/report/1/declaration"
        Then I check "report_declaration_agree"
        And I press "report_declaration_save"
        Then the following fields should have an error:
            | report_declaration_allAgreed_0 |
            | report_declaration_allAgreed_1 |
            | report_declaration_reasonNotAllAgreed |
        When I load the application status from "report-submit-post"

        
    @deputy @wip
    Scenario: If not all agree, need reason
        Given I reset the email log
        When I load the application status from "report-submit-pre"
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I go to "/report/1/declaration"
        Then I check "report_declaration_agree"
        When I fill in the following:
            | report_declaration_allAgreed_1 | 0 |
        And I press "report_declaration_save"
        Then the following fields should have an error:
            | report_declaration_reasonNotAllAgreed |
        When I load the application status from "report-submit-post"


    @deputy @wip
    Scenario: Submit with reason we dont all agree
        Given I reset the email log
        When I load the application status from "report-submit-pre"
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I go to "/report/1/declaration"
        Then I check "report_declaration_agree"
        When I fill in the following:
            | report_declaration_allAgreed_1 | 0 |
        Then I fill in the following:
            | report_declaration_reasonNotAllAgreed | test |
        And I press "report_declaration_save"
        Then the URL should match "/report/\d+/submitted"
        When I load the application status from "report-submit-post"

