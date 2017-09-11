Feature: Report submit (client 1000011)

    @103-6
    Scenario: Submit 103-6 report
        Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-1000014" region
        Then the report should be submittable

    @103-6
    Scenario: pa 103-6 report submission
        Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "pa-report-open" in the "client-1000014" region
        And I click on "edit-report_submit, declaration-page"
        When I fill in the following:
            | report_declaration_agree | 1 |
            | report_declaration_agreedBehalfDeputy_0 | only_deputy |
            | report_declaration_agreedBehalfDeputyExplanation |  |
        And I press "report_declaration_save"
        Then the form should be valid

    @103-6
    Scenario: assert 2nd year 103-6 pa report has been created
        Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "tab-in-progress"
        And I click on "pa-report-open" in the "client-1000014" region
        Then I should see a "#edit-money_in_short" element
        And I should see a "#edit-money_out_short" element
        And I save the application status into "pa-report-103-submitted"
