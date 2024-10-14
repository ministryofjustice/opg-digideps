@v2 @iqpal
Feature: View client details

    @admin @lay-pfa-high-submitted
    Scenario: An admin user cannot see Client details in navigation bar
        Given an admin user accesses the admin app
        Then I should not see "Client details"

    @lay-pfa-high-completed
    Scenario: A Lay user can see Client details in navigation bar
        Given a Lay Deputy has a completed report
        And I visit the report overview page
        Then I should see "Client details"

    @ndr-not-started
    Scenario: A Lay user with a NDR can see Client details in navigation bar
        Given a Lay Deputy has not started an NDR report
        And I view the NDR overview page
        Then I should see "Client details"

    @lay-health-welfare-not-started
    Scenario: A Lay user does not see Client details link on deputyship your details page
        Given a Lay Deputy exists
        Given I view the lay deputy your details page
        Then I should not see the link for client details
