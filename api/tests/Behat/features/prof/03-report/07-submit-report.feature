Feature: Report submit (client 31000010)

    Scenario: balance check and fix
        Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
        And I click on "pa-report-open" in the "client-31000010" region
        Then the prof report should not be submittable
          # check balance mismatch difference
        When I click on "edit-balance"
        Then I should see the "balance-bad" region
        And I should see "£1,030.03" in the "unaccounted-for" region
      # add explanation
        And the step with the following values CAN be submitted:
            | balance_balanceMismatchExplanation | fix prof balance altered by costs |
        Then the URL should match "report/\d+/overview"
        Then the prof report should be submittable

    Scenario: PROF 102-5 Report should be submittable
        Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
        And I click on "pa-report-open" in the "client-31000010" region
        Then the PROF report should be submittable

    Scenario: 102-5 report declaration page
        Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
        And I click on "tab-ready"
        And I click on "pa-report-open" in the "client-31000010" region
        Then I should not see the "download-2016-report" link
        # if not found, it means that the report is not submittable
        And I click on "edit-report_submit"
        Then the URL should match "/report/\d+/review"
        When I click on "declaration-page"
        Then the URL should match "/report/\d+/declaration"
        And each text should be present in the corresponding region:
            | CLY1 hent1                        | client-contact |
            | 078912345678                      | client-contact |
            | cly1@hent.com                     | client-contact |
            | B301QL                            | client-contact |
            | DEP1 SURNAME1                     | deputy-contact |
            | 10000000001                       | deputy-contact |
            | behat-prof1@publicguardian.gov.uk | deputy-contact |
            | Prof OPG                          | deputy-contact |
            | SW3                               | deputy-contact |
        And I should not see the link "edit-deputy-contact"

    Scenario: 102-5 report submission
        Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
        And I click on "pa-report-open" in the "client-31000010" region
        And I click on "edit-report_submit"
        Then each text should be present in the corresponding region:
            | DEP1                            | deputy-firstname |
            | SURNAME1                                 | deputy-lastname |
            | Prof OPG                                  | deputy-address |
            | 10000000001                           | deputy-phone |
            | behat-prof1@publicguardian.gov.uk   | deputy-email |
        And I click on "declaration-page"
        When I fill in the following:
            | report_declaration_agree | 1 |
            | report_declaration_agreedBehalfDeputy_0 | not_deputy |
            | report_declaration_agreedBehalfDeputyExplanation |  |
        And I press "report_declaration_save"
        Then the form should be valid
        And the URL should match "/report/\d+/submitted"
        And I should not see the "report-submit-submitted" link
        # assert report display page is not broken
        When I click on "return-to-reports-page"
        Then the URL should match "/org"
        And the response status code should be 200

    Scenario: 102-5 assert submitted report displays correctly in client profile page
        Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
        And I click on "tab-in-progress"
        And I click on "pa-report-open" in the "client-31000010" region
        And I should see the "view-report" link
        And I save the current URL as "client-31000010-profile"
        # view report
        When I follow "View report"
        Then I should see the "deputy-declaration" region
        And I click on "return-to-client-profile"
        Then the current URL should match with the URL previously saved as "client-31000010-profile"

    Scenario: 102-5 assert 2nd year report has been created and displays correctly
        Given I am logged in as "behat-prof1@publicguardian.gov.uk" with password "DigidepsPass1234"
        And I click on "tab-in-progress"
        And I click on "pa-report-open" in the "client-31000010" region
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
