Feature: Report 103 end

  @deputy
  Scenario: Reload app status after 102 submission for future tests
    # restore status after 102 got submitted (might not be needed, but simpler to keep the journey "102")
    Given I load the application status from "report-submit-reports"
    #And I change the report 1 type to "102" # not needed as the status is form a 102 report





