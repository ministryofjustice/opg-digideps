@v2 @acl @acs
Feature: Limiting access to sections of the app to super admins
  As a super admin user
  In order to prevent other types of users from accessing sensitive or confusing data
  I need to limit access to certain areas of the app to Super Admins

  Scenario: A super admin attempts to access analytics, reports and fixtures
    Given a super admin user accesses the admin app
    When I navigate to the admin analytics page
    Then I should be able to access the "DAT file"
    Then I should be able to access the "satisfaction report"
    Then I should be able to access the "active lays report"
    Then I should be able to access the "user research report"
    Then I should be able to access the fixtures page

  Scenario: An elevated admin attempts to access analytics and reports
    Given an elevated admin user accesses the admin app
    When I navigate to the admin analytics page
    Then I should be able to access the "DAT file"
    Then I should not be able to access the "satisfaction report"
    Then I should not be able to access the "active lays report"
    Then I should not be able to access the "user research report"
    Then I should not be able to access the fixtures page

  Scenario: An admin attempts to access analytics and reports
    Given an admin user accesses the admin app
    When I navigate to the admin analytics page
    Then I should be able to access the "DAT file"
    Then I should not be able to access the "satisfaction report"
    Then I should not be able to access the "active lays report"
    Then I should not be able to access the "user research report"
    Then I should not be able to access the fixtures page
