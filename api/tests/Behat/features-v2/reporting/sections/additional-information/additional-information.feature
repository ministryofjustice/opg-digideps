@v2 @additional-information @acs
Feature: Additional Information

    @lay-pfa-high-not-started
    Scenario: A user has no additional information to add
        Given a Lay Deputy has not started a report
        When I view and start the additional information report section
        And there is no additional information to add
        Then I should be on the additional information summary page
        And the additional information summary page should contain the details I entered

    @lay-pfa-high-not-started
    Scenario: The section navigation links are correctly displayed
        Given a Lay Deputy has not started a report
        When I view the additional information report section
        Then the previous section should be "Actions"
        And the next section should be "Documents"

    @lay-pfa-high-not-started
    Scenario: Adding additional information
        Given a Lay Deputy has not started a report
        When I view and start the additional information report section
        And there is additional information to add
        Then the additional information summary page should contain the details I entered
