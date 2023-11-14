@v2 @v2_admin @organisation-management
Feature: Organisation - Editing an organisation

    @super-admin
    Scenario: A super admin edits an organisation's name
        Given a super admin user accesses the admin app
        When I navigate to the organisations page
        And I navigate to the add organisation page
        When I add an organisation
        Then I should see the organisation
        When I view the organisation
        And I edit the organisation name
        Then I should see the organisation

    @admin
    Scenario: An admin user edits an organisation's name
        Given an admin user accesses the admin app
        When I navigate to the organisations page
        And I navigate to the add organisation page
        When I add an organisation
        Then I should see the organisation
        When I view the organisation
        And I edit the organisation name
        Then I should see the organisation
