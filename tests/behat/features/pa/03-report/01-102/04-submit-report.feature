Feature: Report submit (client 1000014)

    Scenario: 102 report declaration page
        Given I load the application status from "pa-report-completed"
        And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "tab-ready"
        And I click on "pa-report-open" in the "client-1000014" region
        Then I should not see the "download-2016-report" link
        # if not found, it means that the report is not submittable
        And I click on "edit-report_submit"
        Then the URL should match "/report/\d+/review"
        And I click on "declaration-page"
        Then the URL should match "/report/\d+/declaration"
        And I save the page as "report-submit-declaration"

    Scenario: 102 report submission
        Given emails are sent from "deputy" area
        And I reset the email log
        And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-1000014" region
        And I click on "edit-report_submit, declaration-page"
        When I fill in the following:
            | report_declaration_agree | 1 |
            | report_declaration_agreedBehalfDeputy_0 | only_deputy |
            | report_declaration_agreedBehalfDeputyExplanation |  |
        And I press "report_declaration_save"
        Then the form should be valid
        And the URL should match "/report/\d+/submitted"
        And I save the page as "report-submit-submitted"
        And I should not see the "report-submit-submitted" link
        # assert report display page is not broken
        When I click on "return-to-pa-dashboard"
        Then the URL should match "/pa"
        And the response status code should be 200
        And the last email should contain "Thank you for submitting"
        And the last email should have been sent to "behat-pa1@publicguardian.gsi.gov.uk"

    Scenario: 102 assert submitted report displays correctly in client profile page
        Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "tab-in-progress"
        And I click on "pa-report-open" in the "client-1000014" region
        And I should see the "submitted-report-20170528" region
        And I save the current URL as "client-1000014-profile"
        # view report
        When I click on "view-report" in the "submitted-report-20170528" region
        Then I should see the "deputy-declaration" region
        And I click on "return-to-client-profile"
        Then the current URL should match with the URL previously saved as "client-1000014-profile"

    Scenario: 102 assert 2nd year report has been created and displays correctly
        Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "tab-in-progress"
        And I click on "pa-report-open" in the "client-1000014" region
        Then I should see a "#edit-contacts" element
        And I should see a "#edit-decisions" element
        And I should see a "#edit-bank_accounts" element
        And I should see a "#edit-assets" element
        # check bank accounts are added again
        When I follow "edit-bank_accounts"
        Then each text should be present in the corresponding region:
            | HSBC - main account | account-01ca |
            | Current account     | account-01ca |
            | 112233              | account-01ca |
        And I save the application status into "pa-report-submitted"

