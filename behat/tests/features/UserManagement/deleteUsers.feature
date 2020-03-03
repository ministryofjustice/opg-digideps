@userManagement
Feature: Deleting Users
  As a super Admin
  I want to be able to hard delete admin and super admin user accounts
  So that I can ensure we have an up to date user list
  And anyone who has left the OPG or otherwise should not have an account and can not access the system

  Scenario: Create users for scenarios
    Given I am logged in to admin as 'admin@publicguardian.gov.uk' with password 'Abcd1234'
    And the following admins exist:
    | adminType        | firstName     | lastName | email                                 | activated |
    | ROLE_ADMIN       | admin-1       | user     | adminUser1@publicguardian.gov.uk      | true      |
    | ROLE_ADMIN       | admin-2       | user     | adminUser2@publicguardian.gov.uk      | true      |
    | ROLE_SUPER_ADMIN | super-admin-1 | user     | superAdminUser1@publicguardian.gov.uk | true      |
    | ROLE_SUPER_ADMIN | super-admin-2 | user     | superAdminUser2@publicguardian.gov.uk | true      |

  Scenario: Super admin users can delete admin users
    Given I am logged in to admin as 'superAdminUser1@publicguardian.gov.uk' with password 'Abcd1234'
    And I am viewing the edit user page for 'adminUser1@publicguardian.gov.uk'
    Then the url should match "/admin/edit-user"
    When I follow "Delete user"
    Then the url should match "/admin/delete-confirm"
    When I follow "Yes, I'm sure"
    Then the user 'adminUser1@publicguardian.gov.uk' should be deleted

  Scenario: Super admin users can delete super admin users
    Given I am logged in to admin as 'superAdminUser1@publicguardian.gov.uk' with password 'Abcd1234'
    And I am viewing the edit user page for 'superAdminUser2@publicguardian.gov.uk'
    Then the url should match "/admin/edit-user"
    When I follow "Delete user"
    Then the url should match "/admin/delete-confirm"
    When I follow "Yes, I'm sure"
    Then the user 'adminuser1@publicguardian.gov.uk' should be deleted

  Scenario: Admin users cannot delete admin users
    Given I am logged in to admin as 'admin@publicguardian.gov.uk' with password 'Abcd1234'
    And I am viewing the edit user page for 'adminuser2@publicguardian.gov.uk'
    Then the url should match "/admin/edit-user"
    And I should see "Delete user is disabled for non lay deputies, deputies with multiple clients or clients with reports"

  Scenario: Users cannot delete themselves
    Given I am logged in to admin as 'superAdminUser1@publicguardian.gov.uk' with password 'Abcd1234'
    And I am viewing the edit user page for 'superAdminUser1@publicguardian.gov.uk'
    Then the url should match "/admin/edit-user"
    And I should see "Delete user is disabled for non lay deputies, deputies with multiple clients or clients with reports"
