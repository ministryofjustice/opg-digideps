Feature: Report submit

    @deputy
    Scenario: report declaration page
        #Given I set the report 1 end date to 3 days ago
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "report-start"
        Then I should not see the "report-review" link
        # if not found, it means that the report is not submittable
        And I click on "report-submit"
        Then the URL should match "/report/\d+/review"
        And I click on "declaration-page"
        Then the URL should match "/report/\d+/declaration"
        And I save the page as "report-submit-declaration"

    @deputy
    Scenario: report submission
        Given emails are sent from "deputy" area
        And I reset the email log
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I save the application status into "report-submit-pre"
        And I click on "report-start"
        # assert I cannot access the submitted page directly
        And the URL "/report/1/submitted" should not be accessible
        # assert I cannot access the submit page from declaration page
        When I go to "/report/1/declaration"
        Then the URL "/report/1/submitted" should not be accessible
        And I click on "reports, report-start"
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
        And I save the page as "report-submit-submitted"
        # assert report display page is not broken
        When I click on "return-to-reports-page"
        Then the URL should match "/lay"
        And the response status code should be 200
        And the last email should contain "Thank you for submitting"
        #And the last email should have been sent to "behat-user@publicguardian.gsi.gov.uk"
#        And the second_last email should have been sent to "behat-digideps@digital.justice.gov.uk"
#        And the second_last email should contain a PDF of at least 40 kb
        And I save the application status into "report-submit-reports"

    @deputy
    Scenario: admin area check filters, submission and ZIP file content
        Given I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "admin-documents"
        Then I should be on "/admin/documents/list"
        And I save the current URL as "admin-documents-list-new"
        # test filters
        When I click on "tab-archived"
        Then I should see the "report-submission" region exactly 0 times
        When I click on "tab-new"
        Then I should see the "report-submission" region exactly 1 times
        # test search
        When I fill in the following:
            | search | 12345abc |
            | created_by_role | ROLE_PA |
        And I press "search_submit"
        Then I should see the "report-submission" region exactly 0 times
        When I fill in the following:
            | search | 12345abc |
            | created_by_role | ROLE_LAY_DEPUTY |
        And I press "search_submit"
        Then I should see the "report-submission" region exactly 1 times
        # assert submission and download
        Given each text should be present in the corresponding region:
            | Peter White | report-submission-1 |
            | 12345abc | report-submission-1 |
            | 4 documents | report-submission-1 |
        When I click on "download" in the "report-submission-1" region
        Then the page content should be a zip file containing files with the following files:
            | file1.pdf | exactFileName+md5sum | d3f3c05deb6a46cd9e32ea2a1829cf28 |
        #    | file2.pdf | exactFileName+md5sum | 6b871eed6b34b560895f221de1420a5a |
            | DigiRep-.*\.pdf | regexpName+sizeAtLeast | 50000  |
        # test archive
        When I go to the URL previously saved as "admin-documents-list-new"
        When I click on "archive" in the "report-submission-1" region
        Then I should see the "report-submission" region exactly 0 times
        When I click on "tab-archived"
        Then I should see the "report-submission" region exactly 1 times
        And each text should be present in the corresponding region:
            | Peter White | report-submission-1 |
            | 12345abc | report-submission-1 |
            | 4 documents | report-submission-1 |
            | AU | report-submission-1 |

    @deputy
    Scenario: assert 2nd year report has been created
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "report-start"
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
        Then the URL "/report/1/overview" should not be accessible
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

    @deputy
    Scenario: deputy report download
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I click on "report-review"
        Then the URL should match "report/\d+/review"
        And the response should contain "12345ABC"
        And the response should contain "Peter"
        And the response should contain "White"
        # assert documents
        And I should see "file1.pdf" in the "document-list" region
        #And I should see "file2.pdf" in the "document-list" region
        And I should not see "DigiRep" in the "document-list" region
        # test go back link
        When I click on "back-to-reports"
        Then the URL should match "/lay"
        And I should see the "report-download" link