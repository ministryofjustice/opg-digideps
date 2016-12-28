Feature: odr / report submit

    @odr
    Scenario: ODR review page
        Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        # assert URLs not accessible
#        Then the URL "/report/1/submitted" should not be accessible
#        # go to review page
#        When I click on "odr-start, odr-submit"
#        Then the URL should match "/odr/review"
#        # quick check sections are presented. An unit test asserts
#        And I should see an "#visits-care-section" element
#        And I should see an "#assets-section" element
#        And I should see an "#debts-section" element
#        And I should see an "#income-benefits-section" element
#        And I should see an "#expenses-section" element
#        And I should see an "#action-section" element
#        And I should see an "#accounts-section" element
#        # assert pages not accessible
#
#    @odr
#    Scenario: ODR declaration, submission with emails, confirm
#        Given emails are sent from "deputy" area
#        And I reset the email log
#        And I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        And I click on "odr-start, odr-submit, sign-and-submit"
#        Then the URL should match "/odr/declaration"
#        And I save the application status into "odr-submit-pre"
#        #
#        # empty form
#        #
#        When I press "odr_declaration_save"
#        Then the following fields should have an error:
#            | odr_declaration_agree |
#            | odr_declaration_agreedBehalfDeputy_0 |
#            | odr_declaration_agreedBehalfDeputy_1 |
#            | odr_declaration_agreedBehalfDeputy_2 |
#        # missing explanation
#        When I fill in the following:
#            | odr_declaration_agree | 1 |
#            | odr_declaration_agreedBehalfDeputy_2 | more_deputies_not_behalf |
#            | odr_declaration_agreedBehalfDeputyExplanation |  |
#        And I press "odr_declaration_save"
#        Then the following fields should have an error:
#            | odr_declaration_agreedBehalfDeputyExplanation |
#        # change to one deputy and submit
#        When I fill in the following:
#            | odr_declaration_agree | 1 |
#            | odr_declaration_agreedBehalfDeputy_0 | only_deputy |
#            | odr_declaration_agreedBehalfDeputyExplanation |  |
#        And I press "odr_declaration_save"
#        Then the form should be valid
#        And the URL should match "/odr/\d+/submitted"
#        And I save the page as "odr-submit-submitted"
#        And the response status code should be 200
#        # return to homepage
#        When I click on "return-homepage"
#        Then I should be on "/odr"
#        # check emails
#        And the "last" email should have been sent to "behat-user-odr@publicguardian.gsi.gov.uk"
#        And the second_last email should have been sent to "behat-digideps@digital.justice.gov.uk"
#        And the second_last email should contain a PDF of at least 40 kb
#        And I save the application status into "odr-submit-confirmation"
#
#
#    @odr
#    Scenario: check ODR report not accessible after submission
#        Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        And the URL "/odr/1/finance/expenses" should not be accessible
#        And the URL "/odr/1/finance/income-benefits" should not be accessible
#        And the URL "/odr/1/finance/banks" should not be accessible
#        And the URL "/odr/1/assets" should not be accessible
#        And the URL "/odr/1/debts" should not be accessible
#        And the URL "/odr/1/visits-care" should not be accessible
#        And the URL "/odr/1/actions/gifts" should not be accessible
#        And the URL "/odr/1/actions/property" should not be accessible
#        And the URL "/odr/1/actions/info" should not be accessible
#
#    @odr
#    Scenario: ODR homepage and create new report
#        Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        Then I should be on "/odr"
#        And I should see the "reports-history" region
#        # create report
#        When I click on "report-start"
#        Then the URL should match "report/create/\d+"
#        And I save the page as "odr-create-report"
#        # simple validation check. Same form already tested from deputy, not need to check validation cases again
#        When I fill in the following:
#            | report_startDate_day |  |
#            | report_startDate_month |  |
#            | report_startDate_year |  |
#            | report_endDate_day |  |
#            | report_endDate_month |  |
#            | report_endDate_year |  |
#        And I press "report_save"
#        Then the following fields should have an error:
#            | report_startDate_day |
#            | report_startDate_month |
#            | report_startDate_year |
#            | report_endDate_day |
#            | report_endDate_month |
#            | report_endDate_year |
#        And I press "report_save"
#        Then the form should be invalid
#        # valid form
#        Then I fill in the following:
#            | report_startDate_day | 01 |
#            | report_startDate_month | 01 |
#            | report_startDate_year | 2016 |
#            | report_endDate_day | 31 |
#            | report_endDate_month | 12 |
#            | report_endDate_year | 2016 |
#        And I press "report_save"
#        Then the URL should match "report/\d+/overview"
#        # assert homepage with report created
#        When I go to "/"
#        And I click on "report-start"
#        Then the URL should match "report/\d+/overview"
#        And the response status code should be 200

