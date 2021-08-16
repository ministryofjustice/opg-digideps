@contacts @v2
Feature: Contacts

    @super-admin @lay-health-welfare-not-started
    Scenario: A user has no contacts
        Given a Lay Deputy has not started a Health and Welfare report
        When I view and start the contacts report section
        And there are no contacts to add
        Then I should be on the contacts summary page
        And the contacts summary page should contain the details I entered
        When I follow link back to report overview page
        Then I should see "contacts" as "no contacts"

    @super-admin @lay-health-welfare-not-started
    Scenario: Adding one contact
        Given a Lay Deputy has not started a Health and Welfare report
        When I view and start the contacts report section
        And there are contacts to add
        And I enter valid contact details
        And there are no further contacts to add
        Then the contacts summary page should contain the details I entered
        When I follow link back to report overview page
        Then I should see "contacts" as "1 contact"

    @super-admin @lay-health-welfare-not-started
    Scenario: Adding multiple contacts
        Given a Lay Deputy has not started a Health and Welfare report
        When I view and start the contacts report section
        And there are contacts to add
        And I enter valid contact details
        And I enter another contacts details
        And there are no further contacts to add
        Then the contacts summary page should contain the details I entered
        When I follow link back to report overview page
        Then I should see "contacts" as "2 contacts"
