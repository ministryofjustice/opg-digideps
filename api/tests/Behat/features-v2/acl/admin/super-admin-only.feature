Feature: Limiting access to sections of the app to super admins
  As a super admin user
  In order to prevent other types of users from accessing sensitive or confusing data
  I need to limit access to certain areas of the app to Super Admins

  Scenario: Only super admins can access sensitive reports
    Given I am logged in to admin as "super-admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I am on admin page "/admin/stats/metrics"
    And I should see "Download DAT file"
    And I should see "Download satisfaction report"
    And I should see "Download active lays report"
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I am on admin page "/admin/stats/metrics"
    And I should see "Download DAT file"
    And I should not see "Download satisfaction report"
    And I should not see "Download active lays report"

  Scenario: Only super admins can access fixture creation page
    Given I am logged in to admin as "super-admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    Then I should see "Fixtures" in the "navbar" region
    When I follow "Fixtures"
    Then I should be on "/admin/fixtures/"
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    Then I should not see "Fixtures" in the "navbar" region
