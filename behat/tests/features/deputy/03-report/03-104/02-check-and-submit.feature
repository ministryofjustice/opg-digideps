Feature: Report submit

    @deputy @deputy-104
    Scenario: submit 104
        Given I am logged in as "behat-lay-deputy-104@publicguardian.gov.uk" with password "DigidepsPass1234"
        And I click on "report-start, report-submit, declaration-page"
        And I fill in the following:
            | report_declaration_agree | 1 |
            | report_declaration_agreedBehalfDeputy_0 | only_deputy |
            | report_declaration_agreedBehalfDeputyExplanation |  |
        And I press "report_declaration_save"
        Then the form should be valid
        And the URL should match "/report/\d+/submitted"
