@v2 @v2_admin @acl
Feature: Limiting access to sections of the app to super admins
    As a super admin user
    In order to prevent other types of users from accessing sensitive or confusing data
    I need to limit access to certain areas of the app to Super Admins

    @super-admin
    Scenario: A super admin attempts to access analytics, reports, fixtures, notifications and tools
        Given a super admin user accesses the admin app
        When I navigate to the admin analytics page
        Then I should be able to access the "DAT file"
        When I visit the admin stats reports page
        Then I should be able to access the "satisfaction report"
        When I visit the admin stats reports page
        Then I should be able to access the "active lays report"
        When I visit the admin stats reports page
        Then I should be able to access the "user research report"
        When I visit the admin stats reports page
        Then I should be able to access the "inactive admin users report"
        When I visit the admin stats reports page
        Then I should be able to access the 'Fixtures' page
        Then I should be able to access the 'Notifications' page
        Then I should be able to access the 'Tools' page

    @admin-manager
    Scenario: An admin manager attempts to access analytics, reports, fixtures, notifications and tools
        Given an admin manager user accesses the admin app
        When I navigate to the admin analytics page
        Then I should be able to access the "DAT file"
        When I navigate to the admin analytics page
        Then I should not be able to access the "view reports"
        Then I should not be able to access the 'Fixtures' page
        Then I should not be able to access the 'Notifications' page
        Then I should not be able to access the 'Tools' page

    @admin
    Scenario: An admin attempts to access analytics, reports, fixtures, notifications and tools
        Given an admin user accesses the admin app
        When I navigate to the admin analytics page
        Then I should be able to access the "DAT file"
        When I navigate to the admin analytics page
        Then I should not be able to access the "view reports"
        Then I should not be able to access the 'Fixtures' page
        Then I should not be able to access the 'Notifications' page
        Then I should not be able to access the 'Tools' page

    @lay-pfa-high-not-started
    Scenario: A Lay Deputy attempts to access the admin app 
        Given a Lay Deputy attempts to log into the admin app
        Then I should see "You've entered an invalid email or password. Please try again." 
        
        
