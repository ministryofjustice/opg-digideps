Feature: Report submit

    @deputy
    Scenario: report 103 check is complete and submittable
        And I am logged in as "behat-user@publicguardian.gov.uk" with password "Abcd1234"
        And I click on "report-start"
        Then the lay report should be submittable
