@v2 @organisation-management
Feature: Organisation - Deleting an organisation

    @super-admin
    Scenario: A super admin deletes an empty organisation
        Given a super admin user accesses the admin app
        When I navigate to the organisations page
        And I navigate to the add organisation page
        Then I add an organisation
        And I should see the organisation
        When I view the organisation
        Then I should see the organisation is empty
        When I navigate to the organisations page
        And I delete the organisation
        Then I should not see the organisation

    @admin
    Scenario: An admin user deletes an empty organisation
        Given an admin user accesses the admin app
        When I navigate to the organisations page
        And I navigate to the add organisation page
        Then I add an organisation
        And I should see the organisation
        When I view the organisation
        Then I should see the organisation is empty
        And I should not be able to delete the organisation
