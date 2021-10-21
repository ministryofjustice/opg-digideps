@v2 @v2_end_to_end @acs
Feature: An end to end journey from data ingestion to report submission

    @super-admin @lay-combined-high-not-started
    Scenario: CSV is uploaded and report is manually filled in and submitted - Lay
        Given a super admin user accesses the admin app
        And I upload a lay csv that contains a row with deputy email 'aaa@bbb.com'
        When 'aaa@bbb.com' logs in
        And I fill in the accounts section
        And I fill in the actions section
        And I fill in the additional information section
        And I fill in the assets section
        And I fill in the client benefits check section
        And I fill in the contacts section
        And I fill in the debts section
        And I fill in the documents section
        And I fill in the gifts section
        And I fill in the health and lifestyle section
        And I fill in the money in high assets section
        And I fill in the money out section
        And I fill in the visits and care section
        Then I submit the report
