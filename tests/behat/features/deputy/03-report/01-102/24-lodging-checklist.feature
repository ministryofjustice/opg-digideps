Feature: Admin report checklist

  @deputy
  Scenario: Admin completes checklist for the report
    Given I am logged in to admin as "admin@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I go to the URL previously saved as "admin-client-search-client-behat001"
        # reports page
    Then the URL should match "/admin/client/\d+/details"
    And I click on "checklist" in the "report-2016" region
    Then the URL should match "/admin/report/\d+/checklist
