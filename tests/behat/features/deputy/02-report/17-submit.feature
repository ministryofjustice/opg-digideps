#Feature: deputy / report / submit
#
#    @deputy
#    Scenario: report declaration page
#        Given I set the report 1 end date to 3 days ago
#        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        And I click on "reports"
#        Then I should not see the "download-2016-report" link
#        When I click on "report-2016-open"
#        And I save the page as "report-submit-overview-pre-add-further-info"
#        # if not found, it means that the report is not submittable
#        And I follow "edit-report_add_further_info"
#        #And I fill in "report_add_info_furtherInformation" with "test"
#        Then I press "report_add_info_saveAndContinue"
#        Then the URL should match "/report/\d+/declaration"
#        And I save the page as "report-submit-declaration"
#
#    @deputy
#    Scenario: report submission
#        Given emails are sent from "deputy" area
#        And I reset the email log
#        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        And I save the application status into "report-submit-pre"
#        # assert after login I'm redirected to report page
#        Then the URL should match "/report/\d+/overview"
#        # assert I cannot access the submitted page directly
#        And the URL "/report/1/submitted" should not be accessible
#        # assert I cannot access the submit page from declaration page
#        When I go to "/report/1/declaration"
#        Then the URL "/report/1/submitted" should not be accessible
#        And I click on "reports, report-2016-open"
#        # submit without ticking "agree"
#        When I go to "/report/1/declaration"
#        And I press "report_declaration_save"
#        #
#        # empty form
#        #
#        When I press "report_declaration_save"
#        Then the following fields should have an error:
#            | report_declaration_agree |
#            | report_declaration_agreedBehalfDeputy_0 |
#            | report_declaration_agreedBehalfDeputy_1 |
#            | report_declaration_agreedBehalfDeputy_2 |
#        #
#        # missing explanation
#        #
#        #When I check "report_declaration_agree"
#        And I fill in the following:
#            | report_declaration_agree | 1 |
#            | report_declaration_agreedBehalfDeputy_2 | more_deputies_not_behalf |
#            | report_declaration_agreedBehalfDeputyExplanation |  |
#        And I press "report_declaration_save"
#        Then the following fields should have an error:
#            | report_declaration_agreedBehalfDeputyExplanation |
#        #
#        # change to one deputy and submit
#        #
#        When I fill in the following:
#            | report_declaration_agree | 1 |
#            | report_declaration_agreedBehalfDeputy_0 | only_deputy |
#            | report_declaration_agreedBehalfDeputyExplanation |  |
#        And I press "report_declaration_save"
#        Then the form should be valid
#        And the URL should match "/report/\d+/submitted"
#        And I save the page as "report-submit-submitted"
#        # assert report display page is not broken
#        When I click on "return-to-reports-page"
#        Then the URL should match "/reports/\d+"
#        And the response status code should be 200
#        And the last email containing a link matching "/reports/2" should have been sent to "behat-user@publicguardian.gsi.gov.uk"
#        And the second_last email should have been sent to "behat-digideps@digital.justice.gov.uk"
#        And the second_last email should contain a PDF of at least 40 kb
#        And I save the application status into "report-submit-reports"
#
#    @deputy
#    Scenario: submit feedback after report
#        Given emails are sent from "deputy" area
#        And I reset the email log
#        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        And I go to "/report/1/submitted"
#        And I press "feedback_report_save"
#        Then the form should be invalid
#        And fill in "feedback_report_satisfactionLevel_2" with "Neither satisfied or dissatisfied"
#        And I press "feedback_report_save"
#        Then the form should be valid
#        And I should be on "/report/1/submit_feedback"
#        And the last email should contain "Neither satisfied or dissatisfied"
#
#
#    @deputy
#    Scenario: assert 2nd year report has been created
#        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        And I click on "reports, report-open"
#        And I save the page as "report-property-affairs-homepage"
#        Then I should see a "#edit-contacts" element
#        And I should see a "#edit-decisions" element
#        And I should see a "#edit-accounts" element
#        And I should see a "#edit-assets" element
#        When I follow "edit-accounts"
#        And I click on "account-0876"
#        # check no data was previously saved
#        Then the following fields should have the corresponding values:
#            | account_bank  | HSBC main account |
#            | account_openingBalance  | 1,155.00 |
#        When I click on "account-moneyin"
#        Then I should see an "#transactions_transactionsIn_0_amounts_0" element
#        When I click on "account-moneyout"
#        Then I should see an "#transactions_transactionsOut_0_amounts_0" element
#
#
#    @deputy
#    Scenario: assert report is not editable after submission
#        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        When I click on "reports"
#        Then I should not see the "report-2016-edit" link
#        And I should not see the "report-2016" link
#        And I should see the "report-2016-submitted-on" region
#        And the URL "/report/1/overview" should not be accessible
#        And the URL "/report/1/contacts" should not be accessible
#        And the URL "/report/1/decisions" should not be accessible
#        And the URL "/report/1/declaration" should not be accessible
#        And the URL "/report/1/add_further_information" should not be accessible
#        And the URL "/report/1/add_further_information/edit" should not be accessible
#        And the URL "/report/1/accounts" should not be accessible
#        And the URL "/report/1/accounts/banks/upsert/1" should not be accessible
#        And the URL "/report/1/accounts/banks/1/delete" should not be accessible
#        And the URL "/report/1/assets" should not be accessible
#
#    @deputy
#    Scenario: report download
#        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        When I click on "reports"
#        # download report from confirmation page
#        When I go to "/report/1/submitted"
#        When I click on "download-report"
#        And the response should contain "12345ABC"
#        And the response should contain "Peter"
#        And the response should contain "White"
#        # download report from client page
#        #When I go to the homepage
#        When I click on "reports"
#        And I click on "download-2016-report"
#        And the response should contain "12345ABC"
#        And the response should contain "Peter"
#        And the response should contain "White"
#        # test go back link
#        When I click on "back-to-client"
#        Then I should be on "/user-account/client-show"
#
#
#    @deputy
#    Scenario: change report to "not submitted"
#        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        And I change the report "1" submitted to "false"
#
