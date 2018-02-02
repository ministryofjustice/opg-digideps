Feature: Report submit (client 01000011)

    @103-5
    Scenario: Submit 103-5 report
#        Given I am logged in as "behat-prof1@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        And I click on "pa-report-open" in the "client-01000014" region
#        Then the PROF report should be submittable
#
#    @103-5
#    Scenario: prof 103-5 report submission
#        Given I am logged in as "behat-prof1@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        And I click on "pa-report-open" in the "client-01000014" region
#        And I click on "edit-report_submit, declaration-page"
#        When I fill in the following:
#            | report_declaration_agree | 1 |
#            | report_declaration_agreedBehalfDeputy_0 | only_deputy |
#            | report_declaration_agreedBehalfDeputyExplanation |  |
#        And I press "report_declaration_save"
#        Then the form should be valid
#
#    @103-5
#    Scenario: assert 2nd year 103-5 prof report has been created
#        Given I am logged in as "behat-prof1@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        And I click on "tab-in-progress"
#        And I click on "pa-report-open" in the "client-01000014" region
#        Then I should see a "#edit-money_in_short" element
#        And I should see a "#edit-money_out_short" element
