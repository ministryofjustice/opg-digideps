@v2 @organisation-management
Feature: Organisation - Adding an organisation

    @super-admin
    Scenario: A super admin user adds an organisation
        Given a super admin user accesses the admin app
        When I navigate to the organisations page
        And I navigate to the add organisation page
        Then I add an active organisation
        And I should see the organisation
        When I view the organisation
        Then I should see the organisation is empty

    @admin
    Scenario: An admin user adds an organisation
        Given an admin user accesses the admin app
        When I navigate to the organisations page
        And I navigate to the add organisation page
        Then I add an active organisation
        And I should see the organisation
        When I view the organisation
        Then I should see the organisation is empty
