Feature: Report submit (client 01000010)

    Scenario: balance check and fix
        Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-01000010" region
        Then the prof report should not be submittable
          # check balance mismatch difference
        When I click on "edit-balance"
        Then I should see the "balance-bad" region
        And I should see "£475.50" in the "unaccounted-for" region
      # add explanation
        And the step with the following values CAN be submitted:
            | balance_balanceMismatchExplanation | fix prof balance altered by costs |
        Then the URL should match "report/\d+/overview"
        Then the prof report should be submittable

    Scenario: PROF 102-5 Report should be submittable
        Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-01000010" region
        Then the PROF report should be submittable
        And I save the application status into "prof-report-completed"


    Scenario: 102-5 report declaration page
        Given I load the application status from "prof-report-completed"
        And I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
        And I click on "tab-ready"
        And I click on "pa-report-open" in the "client-01000010" region
        Then I should not see the "download-2016-report" link
        # if not found, it means that the report is not submittable
        And I click on "edit-report_submit"
        Then the URL should match "/report/\d+/review"
        And I click on "declaration-page"
        Then the URL should match "/report/\d+/declaration"

    Scenario: 102-5 report submission
        Given emails are sent from "deputy" area
        And I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-01000010" region
        And I click on "edit-report_submit"
        Then each text should be present in the corresponding region:
            | John Named                            | deputy-firstname |
            | Green                                 | deputy-lastname |
            | ADD1                                  | deputy-address |
            | 10000000001                           | deputy-phone |
            | behat-prof1@publicguardian.gov.uk   | deputy-email |
        And I click on "declaration-page"
        When I fill in the following:
            | report_declaration_agree | 1 |
            | report_declaration_agreedBehalfDeputy_0 | only_deputy |
            | report_declaration_agreedBehalfDeputyExplanation |  |
        And I press "report_declaration_save"
        Then the form should be valid
        And the URL should match "/report/\d+/submitted"
        And I should not see the "report-submit-submitted" link
        # assert report display page is not broken
        When I click on "return-to-org-dashboard"
        Then the URL should match "/org"
        And the response status code should be 200
        And the last email should contain "Thank you for submitting"
        And the last email should have been sent to "behat-prof1@publicguardian.gov.uk"


    Scenario: 102-5 assert submitted report displays correctly in client profile page
        Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
        And I click on "tab-in-progress"
        And I click on "pa-report-open" in the "client-01000010" region
        And I should see the "submitted-report-20170319" region
        And I save the current URL as "client-01000010-profile"
        # view report
        When I click on "view-report" in the "submitted-report-20170319" region
        Then I should see the "deputy-declaration" region
        And I click on "return-to-client-profile"
        Then the current URL should match with the URL previously saved as "client-01000010-profile"

    Scenario: 102-5 assert 2nd year report has been created and displays correctly
        Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "Abcd1234"
        And I click on "tab-in-progress"
        And I click on "pa-report-open" in the "client-01000010" region
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
        And I save the application status into "prof-report-submitted"

