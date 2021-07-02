Feature: Report submit (client 02100014)

    Scenario: Setup data
        Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
        Given the following users exist:
          | ndr      | deputyType     | firstName | lastName | email                              | postCode | activated |
          | disabled | PA_TEAM_MEMBER | Kim       | Petras   | KimPetrasTeamMember@behat-test.com | HA4      | true      |
        And the following users are in the organisations:
          | userEmail                 | orgName |
          | KimPetrasTeamMember@behat-test.com   | PA OPG  |

    Scenario: 102 report declaration page
        And I am logged in as "KimPetrasTeamMember@behat-test.com" with password "DigidepsPass1234"
        And I click on "tab-ready"
        And I fill in "search" with "02100014"
        And I press "search_submit"
        And I click on "pa-report-open" in the "client-02100014" region
        Then I should not see the "download-2016-report" link
        # if not found, it means that the report is not submittable
        And I click on "edit-report_submit"
        Then the URL should match "/report/\d+/review"
        When I click on "declaration-page"
        Then the URL should match "/report/\d+/declaration"
        And each text should be present in the corresponding region:
            | CLY7 hent                       | client-contact |
            | 078912345678                    | client-contact |
            | cly7@hent.com                   | client-contact |
            | B301QL                          | client-contact |
            | DEP1 SURNAME1                   | deputy-contact |
            | +4410000000001                  | deputy-contact |
            | behat-pa1@publicguardian.gov.uk | deputy-contact |
            | The OPG                          | deputy-contact |
            | SW3                             | deputy-contact |
        And I should not see the link "edit-deputy-contact"


    Scenario: 102 report submission
        # log in as team member to submit the report and test that named deputy details are displayed
        Given I am logged in as "KimPetrasTeamMember@behat-test.com" with password "DigidepsPass1234"
        And I fill in "search" with "02100014"
        And I press "search_submit"
        When I click on "pa-report-open" in the "client-02100014" region
        And I should see "Ready to submit" in the "report-detail-status" region
        And I click on "edit-report_submit"
        Then each text should be present in the corresponding region:
            | DEP1                                            | deputy-firstname |
            | SURNAME1                                        | deputy-lastname  |
            | +4410000000001                                  | deputy-phone     |
            | behat-pa1@publicguardian.gov.uk                 | deputy-email     |
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
        When I click on "return-to-reports-page"
        Then the URL should match "/org"
        And the response status code should be 200

    Scenario: 102 assert submitted report displays correctly in client profile page
        Given I am logged in as "KimPetrasTeamMember@behat-test.com" with password "DigidepsPass1234"
        And I click on "tab-in-progress"
        And I fill in "search" with "02100014"
        And I press "search_submit"
        And I click on "pa-report-open" in the "client-02100014" region
        And I should see the "submitted-report-20170528" region
        And I save the current URL as "client-02100014-profile"
        # view report
        When I click on "view-report" in the "submitted-report-20170528" region
        Then I should see the "deputy-declaration" region
        And I click on "return-to-client-profile"
        Then the current URL should match with the URL previously saved as "client-02100014-profile"

    Scenario: 102 assert 2nd year report has been created and displays correctly
        Given I am logged in as "KimPetrasTeamMember@behat-test.com" with password "DigidepsPass1234"
        And I click on "tab-in-progress"
        And I fill in "search" with "02100014"
        And I press "search_submit"
        And I click on "pa-report-open" in the "client-02100014" region
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
