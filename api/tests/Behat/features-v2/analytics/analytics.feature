@v2 @v2_admin @analytics
Feature: Analytics - view and download analytics

#    Covering all user types here due to data creating in parallel runs breaking tests
    @super-admin @admin-manager @admin
    Scenario: A super admin user views various date ranges on the analytics page
        Given a super admin user accesses the admin app
        And reports exist that were submitted '20' years ago
        When I visit the admin analytics page
        When I change reporting period to apply only to our generated data
        Then I should see the correct metric values displayed
        When I add more clients, deputies and reports
        And I change reporting period to apply only to our generated data
        Then I should see the correct metric values displayed

        Given an admin manager user accesses the admin app
        When I visit the admin analytics page
        When I change reporting period to apply only to our generated data
        Then I should see the correct metric values displayed

        Given an admin user accesses the admin app
        When I visit the admin analytics page
        When I change reporting period to apply only to our generated data
        Then I should see the correct metric values displayed

    @super-admin
    Scenario: A admin super user downloads files from the analytics page
        Given a super admin user accesses the admin app
        When I visit the admin analytics page
        Then I should see the correct options in the actions dropdown
        When I try to download the DAT file
        Then I should have no issues downloading the file
        When I try to view the reports page
        Then I should have no issues viewing the page
        When I try to download user research report
        Then I should have no issues downloading the file
        When I try to download satisfaction report
        Then I should have no issues downloading the file
        When I try to download active lays report
        Then I should have no issues downloading the file

    @admin-manager
    Scenario: A admin manager downloads files from the analytics page
        Given an admin manager user accesses the admin app
        When I visit the admin analytics page
        Then I should only see the download DAT button
        When I try to download the DAT file
        Then I should have no issues downloading the file

    @admin
    Scenario: A admin user downloads files from the analytics page
        Given an admin user accesses the admin app
        When I visit the admin analytics page
        Then I should only see the download DAT button
        When I try to download the DAT file
        Then I should have no issues downloading the file
