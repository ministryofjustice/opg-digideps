Feature: Report submit

    @deputy
    Scenario: report declaration page
        Given I am logged in as "behat-lay-deputy-102@publicguardian.gov.uk" with password "Abcd1234"
        And I click on "report-start"
        Then I should not see the "report-review" link
        # if not found, it means that the report is not submittable
        And I click on "report-submit"
        Then the URL should match "/report/\d+/review"
        And I click on "declaration-page"
        Then the URL should match "/report/\d+/declaration"

    @deputy
    Scenario: report submission
        Given emails are sent from "deputy" area
        And I am logged in as "behat-lay-deputy-102@publicguardian.gov.uk" with password "Abcd1234"
        And I save the application status into "report-submit-pre"
        And I click on "report-start"
        And I save the report as "102 report"
        # assert I cannot access the submitted page directly
        Then the report URL "submitted" for "102 report" should not be accessible
        When I click on "reports, report-start"
        # submit without ticking "agree"
        When I go to the report URL "declaration" for "102 report"
        And I press "report_declaration_save"
        #
        # empty form
        #
        Then the following fields should have an error:
            | report_declaration_agree |
            | report_declaration_agreedBehalfDeputy_0 |
            | report_declaration_agreedBehalfDeputy_1 |
            | report_declaration_agreedBehalfDeputy_2 |
            | report_declaration_agreedBehalfDeputyExplanation |
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
        # assert report display page is not broken
        When I click on "return-to-reports-page"
        Then the URL should match "/lay"
        And the response status code should be 200
        And the last email should contain "Thank you for submitting"
        And the last email should contain "next annual deputy report (for 01/01/2017 to 31/12/2017)"
        And I save the application status into "report-submit-reports"
        And the report URL "overview" for "102 report" should not be accessible
        And the report URL "decisions/summary" for "102 report" should not be accessible
        And the report URL "contacts/summary" for "102 report" should not be accessible
        And the report URL "visits-care/summary" for "102 report" should not be accessible
        And the report URL "bank-accounts/summary" for "102 report" should not be accessible
        And the report URL "money-transfers/summary" for "102 report" should not be accessible
        And the report URL "money-in/summary" for "102 report" should not be accessible
        And the report URL "money-out/summary" for "102 report" should not be accessible
        And the report URL "balance" for "102 report" should not be accessible
        And the report URL "assets/summary" for "102 report" should not be accessible
        And the report URL "debts/summary" for "102 report" should not be accessible
        And the report URL "actions" for "102 report" should not be accessible
        And the report URL "declaration" for "102 report" should not be accessible

    @deputy
    Scenario: deputy gives feedback after submitting report
        Given emails are sent from "deputy" area
        And I load the application status from "report-submit-pre"
        And I am logged in as "behat-lay-deputy-102@publicguardian.gov.uk" with password "Abcd1234"
        And I click on "report-start"
        And I click on "report-submit"
        And I click on "declaration-page"
        And I fill in the following:
            | report_declaration_agree | 1 |
            | report_declaration_agreedBehalfDeputy_0 | only_deputy |
            | report_declaration_agreedBehalfDeputyExplanation |  |
        And I press "report_declaration_save"
        Then the form should be valid
        And the URL should match "/report/\d+/submitted"
        When I press "feedback_report_save"
        Then the following fields should have an error:
            | feedback_report_satisfactionLevel_0 |
            | feedback_report_satisfactionLevel_1 |
            | feedback_report_satisfactionLevel_2 |
            | feedback_report_satisfactionLevel_3 |
            | feedback_report_satisfactionLevel_4 |
        When I fill in the following:
            | feedback_report_satisfactionLevel_0 | 5 |
        And I fill in the following:
            | feedback_report_comments | Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. |
        And I press "feedback_report_save"
        Then the form should be valid
        And the URL should match "/report/\d+/submit_feedback"
        When I click on "return-to-reports-page"
        Then the URL should match "/lay"
        And the response status code should be 200
        And the last email should contain "Very satisfied"
        And the last email should contain "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."

    @deputy
    Scenario: admin area check filters, submission and ZIP file content
        Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
        And I click on "admin-documents"
        Then I should be on "/admin/documents/list"
        And I save the current URL as "admin-documents-list-new"
        # test search
        When I fill in the following:
            | search | behat102 |
            | created_by_role | ROLE_PA_% |
        And I press "search_submit"
        Then I should see the "report-submission" region exactly 0 times
        When I fill in the following:
            | search | 102 |
            | created_by_role | ROLE_LAY_DEPUTY |
        And I press "search_submit"
        Then I should see the "report-submission" region exactly 1 times
        # assert submission and download
        Given each text should be present in the corresponding region:
            | John 102 | report-submission-1 |
            | 102 | report-submission-1 |
            | Report + docs | report-submission-1 |
        When I check "Select 102"
        Then I click on "download"
        # only checks one level deep. In this case, we check for a single report zip file
        And the page content should be a zip file containing files with the following files:
            | Report_102_2016_2016_.*.zip | regexpName+sizeAtLeast | 60000 |
        # test archive
        When I go to the URL previously saved as "admin-documents-list-new"
        Then I check "Select 102"
        When I click on "archive"
        And I click on "tab-archived"
        Then each text should be present in the corresponding region:
            | John 102 | report-submission-1 |
            | 102 | report-submission-1 |
            | Report + docs | report-submission-1 |
            | AU | report-submission-1 |

    @deputy
    Scenario: assert 2nd year report has been created
        Given I am logged in as "behat-lay-deputy-102@publicguardian.gov.uk" with password "Abcd1234"
        And I click on "report-start"
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
            | Balance for 31 December 2017 required | account-02ca |

    @deputy
    Scenario: deputy report download
        Given I am logged in as "behat-lay-deputy-102@publicguardian.gov.uk" with password "Abcd1234"
        When I click on "report-review"
        Then the URL should match "report/\d+/review"
        And the response should contain "102"
        And the response should contain "John"
        And the response should contain "102-client"
        # assert documents
        And I should see "file1.pdf" in the "document-list" region
        #And I should see "file2.pdf" in the "document-list" region
        And I should not see "DigiRep" in the "document-list" region
        And I should not see "DigiRepTransactions" in the "document-list" region
        # test go back link
        When I click on "back-to-reports"
        Then the URL should match "/lay"
        And I should see the "report-download" link
