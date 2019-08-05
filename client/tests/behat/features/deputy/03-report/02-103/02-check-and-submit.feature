Feature: Report submit

    @deputy @deputy-103
    Scenario: report 103 check is complete and submittable
        Given I am logged in as "behat-lay-deputy-103@publicguardian.gov.uk" with password "Abcd1234"
        And I click on "report-start"
        Then the lay report should be submittable
