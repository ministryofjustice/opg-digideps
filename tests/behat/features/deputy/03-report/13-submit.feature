Feature: deputy / report / submit

    @deputy
    Scenario: report declaration page
#        Given I set the report 1 end date to 3 days ago
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports, report-2016"
        Then I should not see the "download-2016-report" link
        And I save the page as "report-submit-overview-pre-add-further-info"
        # if not found, it means that the report is not submittable
        And I follow "edit-report_add_further_info"
        #And I fill in "report_add_info_furtherInformation" with "test"
        Then I press "report_add_info_saveAndContinue"
        And the form should be valid
        Then the URL should match "/report/\d+/declaration"
        And I save the page as "report-submit-declaration"

    @deputy
    Scenario: report submission
        Given emails are sent from "deputy" area
        And I reset the email log
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I save the application status into "report-submit-pre"
        # assert after login I'm redirected to report page
        Then the URL should match "/report/\d+/overview"
        # assert I cannot access the submitted page directly
        And the URL "/report/1/submitted" should not be accessible
        # assert I cannot access the submit page from declaration page
        When I go to "/report/1/declaration"
        Then the URL "/report/1/submitted" should not be accessible
        And I click on "reports, report-2016"
        # submit without ticking "agree"
        When I go to "/report/1/declaration"
        And I press "report_declaration_save"
        #
        # empty form
        #
        When I press "report_declaration_save"
        Then the following fields should have an error:
            | report_declaration_agree |
            | report_declaration_agreedBehalfDeputy_0 |
            | report_declaration_agreedBehalfDeputy_1 |
            | report_declaration_agreedBehalfDeputy_2 |
        #
        # missing explanation
        #
        #When I check "report_declaration_agree"
        And I fill in the following:
            | report_declaration_agree | 1 |
            | report_declaration_agreedBehalfDeputy_2 | more_deputies_not_behalf |
            | report_declaration_agreedBehalfDeputyExplanation |  |
        And I press "report_declaration_save"
        Then the following fields should have an error:
            | report_declaration_agreedBehalfDeputyExplanation |
        #
        # change to one deputy and submit
        #
        When I fill in the following:
            | report_declaration_agree | 1 |
            | report_declaration_agreedBehalfDeputy_0 | only_deputy |
            | report_declaration_agreedBehalfDeputyExplanation |  |
        And I press "report_declaration_save"
        Then the form should be valid
        And the URL should match "/report/\d+/submitted"
        And I save the page as "report-submit-submitted"
        # assert report display page is not broken
        When I click on "return-to-reports-page"
        Then the URL should match "/reports/\d+"
        And the response status code should be 200
        And the last email containing a link matching "/reports/2" should have been sent to "behat-user@publicguardian.gsi.gov.uk"
        And the second_last email should have been sent to "behat-digideps@digital.justice.gov.uk"
        And the second_last email should contain a PDF of at least 40 kb
        And I save the application status into "report-submit-reports"


    @deputy
    Scenario: assert 2nd year report has been created
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports, report-open"
        And I save the page as "report-property-affairs-homepage"
        Then I should see a "#edit-contacts" element
        And I should see a "#edit-decisions" element
        And I should see a "#edit-bank_accounts" element
        And I should see a "#edit-assets" element
        # check bank accounts are added again
        When I follow "edit-bank_accounts"
        Then each text should be present in the corresponding region:
            | HSBC - saving account | account-02ca |
            | Saving account        | account-02ca |
            | 445566                | account-02ca |


    @deputy
    Scenario: assert report is not editable after submission
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I click on "reports"
        Then I should not see the "report-2016-edit" link
        And I should not see the "report-2016" link
        And I should see the "report-2016-submitted-on" region
        And the URL "/report/1/overview" should not be accessible
        And the URL "/report/1/decisions/summary" should not be accessible
        And the URL "/report/1/contacts/summary" should not be accessible
        And the URL "/report/1/visits-care/summary" should not be accessible
        And the URL "/report/1/bank-accounts/summary" should not be accessible
        And the URL "/report/1/money-transfers/summary" should not be accessible
        And the URL "/report/1/money-in/summary" should not be accessible
        And the URL "/report/1/money-out/summary" should not be accessible
        And the URL "/report/1/balance" should not be accessible
        And the URL "/report/1/assets/summary" should not be accessible
        And the URL "/report/1/debts/summary" should not be accessible
        And the URL "/report/1/actions" should not be accessible
        And the URL "/report/1/declaration" should not be accessible
        And the URL "/report/1/add_further_information" should not be accessible
        And the URL "/report/1/add_further_information/edit" should not be accessible

    @deputy
    Scenario: report download
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I click on "reports, download-2016-to-2017-report"
        And the response should contain "12345ABC"
        And the response should contain "Peter"
        And the response should contain "White"
        # test go back link
        When I click on "back-to-reports"
        Then I should see the "download-2016-to-2017-report" link
