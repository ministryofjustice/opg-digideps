Feature: deputy / report / mental capacity

    @deputy
    Scenario: mental capacity add 
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports,report-2016-open, edit-mental_capacity"
        # submit empty form
        And I press "mental_capacity_save"
        Then the following fields should have an error:
            | mental_capacity_hasCapacityChanged_0 |
            | mental_capacity_hasCapacityChanged_1 |
        # no details
        When I fill in the following:
            | mental_capacity_hasCapacityChanged_0      | changed |
            | mental_capacity_hasCapacityChangedDetails |   | 
        And I press "mental_capacity_save"
        Then the following fields should have an error:
            | mental_capacity_hasCapacityChangedDetails |
        # form correct
        Then I fill in the following:
            | mental_capacity_hasCapacityChanged_0      | changed |
            | mental_capacity_hasCapacityChangedDetails | ccd  | 
        And I press "mental_capacity_save"
        Then the form should be valid
     
    @deputy
    Scenario: mental capacity check previously added info and edit 
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I click on "reports,report-2016-open, edit-mental_capacity"
        # check values
        Then the following fields should have the corresponding values:
            | mental_capacity_hasCapacityChanged_0      | changed |
            | mental_capacity_hasCapacityChangedDetails | ccd  | 
        # edit
        When I fill in the following:
            | mental_capacity_hasCapacityChanged_1      | stayedSame |
            | mental_capacity_hasCapacityChangedDetails |   | 
        And I press "mental_capacity_save"
        Then the form should be valid

        
