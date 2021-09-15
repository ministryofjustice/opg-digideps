@v2 @actions
Feature: Actions (NDR)

    @ndr-not-started
    Scenario: A user has no gifts or property decisions to make
        Given a Lay Deputy has not started an NDR report
        And I view and start the actions report section
        When I choose no and save on gifts actions section
        And I choose "no" and save on the "property-maintenance" page
        And I choose "no" and save on the "property-selling-rent" page
        And I choose "no" and save on the "property-buy" page
        Then I should be on the actions report summary page
        When I follow link back to report overview page
        And I should see "actions" as "finished"

    @ndr-not-started
    Scenario: A user has made a gift and filled in the details along with some property decisions
        Given a Lay Deputy has not started an NDR report
        And I view and start the actions report section
        When I choose yes and fill in details about the gifts and then save on gifts actions section
        And I choose "yes" and save on the "property-maintenance" page
        And I choose "yes" and save on the "property-selling-rent" page
        And I choose "yes" and save on the "property-buy" page
        Then I should be on the actions report summary page
        And I should see the expected action report section responses
