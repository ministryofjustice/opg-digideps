Feature: Report submit

    @deputy
    Scenario: submit 104
        Given emails are sent from "deputy" area
        And I am logged in as "behat-user@publicguardian.gov.uk" with password "Abcd1234"
        And I click on "report-start, report-submit, declaration-page"
        And I fill in the following:
            | report_declaration_agree | 1 |
            | report_declaration_agreedBehalfDeputy_0 | only_deputy |
            | report_declaration_agreedBehalfDeputyExplanation |  |
        And I press "report_declaration_save"
        Then the form should be valid
        And the URL should match "/report/\d+/submitted"
        And the last email should contain "next annual deputy report (for 01/01/2017 to 31/12/2017)"
