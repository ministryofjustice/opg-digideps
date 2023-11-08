@v2 @v2_admin @organisation-management
Feature: Organisation - Adding a user to the organisation

    @super-admin
    Scenario: A super admin user adds a professional user to an org
        Given a super admin user accesses the admin app
        When I navigate to the organisations page
        And I navigate to the add organisation page
        When I add an organisation
        Then I should see the organisation
        When I view the organisation
        Then I should see the organisation is empty
        When I add 1 professional users to the organisation
        Then I should see the organisation has 1 users

    @admin
    Scenario: An admin user adds two professional users to an org
        Given an admin user accesses the admin app
        When I navigate to the organisations page
        And I navigate to the add organisation page
        When I add an organisation
        Then I should see the organisation
        When I view the organisation
        Then I should see the organisation is empty
        When I add 2 professional users to the organisation
        Then I should see the organisation has 2 users

    @super-admin @lay-pfa-high-not-started
    Scenario: A super admin user adds a lay deputy to an org
        Given a super admin user accesses the admin app
        When I navigate to the organisations page
        And I navigate to the add organisation page
        When I add an organisation
        Then I should see the organisation
        When I view the organisation
        Then I should see the organisation is empty
        When I add a lay user to the organisation
        Then I should see an unsuitable role error
        And I should see the organisation is empty
