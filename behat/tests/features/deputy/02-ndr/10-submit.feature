Feature: ndr / report submit

    @ndr
    Scenario: NDR review page
        Given I am logged in as "behat-lay-deputy-ndr@publicguardian.gov.uk" with password "Abcd1234"
        # go to review page
        When I click on "ndr-start, ndr-submit"
        Then the URL should match "/ndr/\d+/review"
        # quick check sections are presented. An unit test asserts
        And I should see an "#visits-care-section" element
        And I should see an "#assets-section" element
        And I should see an "#debts-section" element
        And I should see an "#income-benefits-section" element
        And I should see an "#expenses-section" element
        And I should see an "#action-section" element
        And I should see an "#accounts-section" element
        # assert pages not accessible

    @ndr
    Scenario: NDR declaration and submission
        Given emails are sent from "deputy" area
        And I am logged in as "behat-lay-deputy-ndr@publicguardian.gov.uk" with password "Abcd1234"
        And I click on "ndr-start, ndr-submit, ndr-declaration-page"
        Then the URL should match "/ndr/\d+/declaration"
        #
        # empty form
        #
        When I press "ndr_declaration_save"
        Then the following fields should have an error:
            | ndr_declaration_agree |
            | ndr_declaration_agreedBehalfDeputy_0 |
            | ndr_declaration_agreedBehalfDeputy_1 |
            | ndr_declaration_agreedBehalfDeputy_2 |
            | ndr_declaration_agreedBehalfDeputyExplanation |
        # missing explanation
        When I fill in the following:
            | ndr_declaration_agree | 1 |
            | ndr_declaration_agreedBehalfDeputy_2 | more_deputies_not_behalf |
            | ndr_declaration_agreedBehalfDeputyExplanation |  |
        And I press "ndr_declaration_save"
        Then the following fields should have an error:
            | ndr_declaration_agreedBehalfDeputyExplanation |
        # change to one deputy and submit
        When I fill in the following:
            | ndr_declaration_agree | 1 |
            | ndr_declaration_agreedBehalfDeputy_0 | only_deputy |
            | ndr_declaration_agreedBehalfDeputyExplanation |  |
        And I press "ndr_declaration_save"
        Then the form should be valid
        And the URL should match "/ndr/\d+/submitted"
        And the response status code should be 200
        When I save the report as "submitted NDR report"
        Then the report URL "visits-care/summary" for "submitted NDR report" should not be accessible
        And the report URL "deputy-expenses/summary" for "submitted NDR report" should not be accessible
        And the report URL "income-benefits/summary" for "submitted NDR report" should not be accessible
        And the report URL "bank-accounts/summary" for "submitted NDR report" should not be accessible
        And the report URL "assets/summary" for "submitted NDR report" should not be accessible
        And the report URL "debts/summary" for "submitted NDR report" should not be accessible
        And the report URL "actions/summary" for "submitted NDR report" should not be accessible
        And the report URL "any-other-info/summary" for "submitted NDR report" should not be accessible
        # return to homepage
        When I click on "return-to-reports-page"
        Then I should be on "/ndr"

    @ndr
    Scenario: admin area check submission ZIP file
        Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
        And I click on "admin-documents"
        Then I should be on "/admin/documents/list"
        And I save the current URL as "ndr-admin-documents-list-new"
            # assert submission and download
        Then I should see "John ndr-client"
        And I should see "ndr"
        When I check "Select ndr"
        Then I click on "download"
        # only checks one level deep. In this case, we check for a single report zip file
        And the page content should be a zip file containing files with the following files:
            | NdrRep.*.zip | regexpName+sizeAtLeast | 38000 |
        # test archive
        When I go to the URL previously saved as "ndr-admin-documents-list-new"
        Then I check "Select ndr"
        When I click on "archive"
        And I click on "tab-archived"
        Then I should see the "report-submission" region exactly 1 times
        Then I should see "John ndr-client"
        And I should see "ndr"

    @ndr
    Scenario: assert 2nd year report has been created
        Given I am logged in as "behat-lay-deputy-ndr@publicguardian.gov.uk" with password "Abcd1234"
        And I click on "report-start"
        Then I should see a "#edit-contacts" element
        And I should see a "#edit-decisions" element
        And I should see a "#edit-bank_accounts" element
        And I should see a "#edit-assets" element
        # check bank accounts are added again
        When I follow "edit-bank_accounts"
        Then each text should be present in the corresponding region:
            | Court Funds Office account | account-11cf |
            | Saving account        | account-02ca |
            | 445566                | account-02ca |
            | HSBC - saving account | account-02ca |
            | Saving account        | account-02ca |
            | 445566                | account-02ca |
        Then I click on "breadcrumbs-report-overview"
        And I follow "edit-assets"
        And each text should be present in the corresponding region:
            | Yes                    | has-assets                   |
            | Alfa Romeo 147 JTD     | asset-alfa-romeo-147-jtd     |
            | £17,500.00             | asset-alfa-romeo-147-jtd     |
            | 11 March 2015          | asset-alfa-romeo-147-jtd     |
            | Impressionist painting | asset-impressionist-painting |
            | £25,010.00             | asset-impressionist-painting |
            | £42,510                | asset-total                 |

    @ndr
    Scenario: NDR homepage and create new report
        Given I am logged in as "behat-lay-deputy-ndr@publicguardian.gov.uk" with password "Abcd1234"
        Then I should be on "/ndr"
        And I should see the "reports-history" region
        # edit report period
        And I click on "report-edit-period-inline"
        # check the form loads the right value
        Then the following fields should have the corresponding values:
            | report_edit_startDate_day   | 02   |
            | report_edit_startDate_month | 11   |
            | report_edit_startDate_year  | 2018 |
            | report_edit_endDate_day     | 01   |
            | report_edit_endDate_month   | 11   |
            | report_edit_endDate_year    | 2019 |

