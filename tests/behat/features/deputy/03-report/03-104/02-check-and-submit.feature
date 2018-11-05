Feature: Report submit

    @deputy
    Scenario: submit 104
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "report-start, report-submit, declaration-page"
        And I fill in the following:
            | report_declaration_agree | 1 |
            | report_declaration_agreedBehalfDeputy_0 | only_deputy |
            | report_declaration_agreedBehalfDeputyExplanation |  |
        And I press "report_declaration_save"
        Then the form should be valid
        And the URL should match "/report/\d+/submitted"
        #And I save the application status into "report-104-submitted"
