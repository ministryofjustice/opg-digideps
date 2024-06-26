@v2 @v2_reporting_1 @additional-information
Feature: Additional Information (NDR)

    @ndr-not-started
    Scenario: A user has no additional information to add
        Given a Lay Deputy has not started an NDR report
        When I view and start the additional information report section
        And there is no additional information to add
        Then I should be on the additional information summary page
        And the additional information summary page should contain the details I entered

    @ndr-not-started
    Scenario: Adding additional information
        Given a Lay Deputy has not started an NDR report
        When I view and start the additional information report section
        And there is additional information to add
        Then the additional information summary page should contain the details I entered
