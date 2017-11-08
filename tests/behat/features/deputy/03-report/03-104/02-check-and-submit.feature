Feature: Report submit

    @deputy
    Scenario: report 104 check is complete and not submittable
        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "report-start"
        And the lay report should be submittable
