Feature: Report submit

    @deputy
    Scenario: report 103 check is complete and submittable
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then the report should be submittable
        And I change the report 1 type to "102"
        Then the report should be submittable
        And I change the report 1 type to "103"


