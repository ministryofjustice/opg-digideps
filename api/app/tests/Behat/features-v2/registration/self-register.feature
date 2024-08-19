@v2 @v2_sequential_1 @registration @self-register
Feature: Lay Deputy Self Registration

    @super-admin
    Scenario: A Lay user entering an invalid case number cannot self register
        Given a csv has been uploaded to the sirius bucket with the file 'lay-4-valid-rows.csv'
        When I run the lay CSV command the file contains 4 new pre-registration entities
        And a Lay Deputy registers to deputise for a client with an invalid case number
        Then I should see an 'invalid case number' error

    @super-admin
    Scenario: A Lay user entering an valid case number and invalid case details cannot self register
        Given a csv has been uploaded to the sirius bucket with the file 'lay-4-valid-rows.csv'
        When I run the lay CSV command the file contains 4 new pre-registration entities
        And a Lay Deputy registers to deputise for a client with a valid case number and invalid case details
        And I should see an 'invalid deputy firstname' error
        And I should see an 'invalid deputy lastname' error
        And I should see an 'invalid deputy postcode' error
        Then I should see an 'invalid client lastname' error

    @super-admin
    Scenario: A Lay user with an existing pre-registration record can self register
        Given a csv has been uploaded to the sirius bucket with the file 'lay-4-valid-rows-different-first-names.csv'
        When I run the lay CSV command the file contains 4 new pre-registration entities
        And a Lay Deputy registers to deputise for a client with valid details
        Then my deputy details should be saved to my account
        And I should be on the Lay homepage

    @super-admin
    Scenario: A Lay user inputting unicode characters can self register
        Given a csv has been uploaded to the sirius bucket with the file 'lay-1-row-special-chars.csv'
        When I run the lay CSV command the file contains a new pre-registration entity with special characters
        And a Lay Deputy registers with valid details using unicode characters
        Then my deputy details should be saved to my account
        And I should be on the Lay homepage

    @super-admin
    Scenario: A Lay user with an existing pre-registration record and a user account created by a case manager can register
        Given a csv has been uploaded to the sirius bucket with the file 'lay-4-valid-rows-different-first-names.csv'
        Given a super admin user accesses the admin app
        When I run the lay CSV command the file contains 4 new pre-registration entities
        And I create a Lay Deputy user account for one of the deputies in the CSV
        When a Lay Deputy clicks the activation link in the registration email
        And I complete the case manager user registration flow with valid deputyship details
        Then my deputy details should be saved to my account
        And I should be on the Lay homepage

    @admin
    Scenario: A Co-deputy can register for the service and is searchable in admin
        Given a csv has been uploaded to the sirius bucket with the file 'lay-2-rows-co-deputy.csv'
        When I run the lay CSV command the file contains 2 new pre-registration entities for the same case
        And one of the Lay Deputies registers to deputise for a client with valid details
        Then my deputy details should be saved to my account
        And I should be on the Lay homepage
        When I invite a Co-Deputy to the service
        Then they should be able to register to deputise for a client with valid details
        Then the co-deputy details should be saved to the co-deputy's account
        And they should be on the Lay homepage
        Given an admin user accesses the admin app
        When I visit the admin Search Users page
        And I search for the co-deputy using their email address
        Then the co-deputy should appear in the search results

    @super-admin
    Scenario: A Lay user with the same verification details cannot be uniquely identified
        Given a csv has been uploaded to the sirius bucket with the file 'lay-2-valid-rows-not-unique.csv'
        When I run the lay CSV command the file contains 2 new pre-registration entities
        And a Lay Deputy registers to deputise for a client with details that are not unique
        Then I should see a 'deputy not uniquely identified' error

    @super-admin
    Scenario: A Lay user with an existing pre-registration record that is not unique and a user account created by a case manager cannot register
        Given a csv has been uploaded to the sirius bucket with the file 'lay-2-valid-rows-not-unique.csv'
        Given a super admin user accesses the admin app
        When I run the lay CSV command the file contains 2 new pre-registration entities
        Given I create a Lay Deputy user account for one of the deputies in the CSV that are not unique
        When a Lay Deputy clicks the activation link in the registration email
        And I complete the case manager user registration flow with deputyship details that are not unique
        Then I should see a 'deputy not uniquely identified' error

    @super-admin
    Scenario: A Co-deputy cannot register for the service with details that already registered
        Given a csv has been uploaded to the sirius bucket with the file 'lay-2-rows-co-deputy-change-details.csv'
        Given a super admin user accesses the admin app
        When I run the lay CSV command the file contains 2 new pre-registration entities for the same case
        And I create a Lay Deputy user account for one of the deputies in the co-deputy CSV
        When a Lay Deputy clicks the activation link in the registration email
        And I complete the case manager user registration flow with other deputy valid deputyship details
        Then my deputy details should be saved to my account
        And I should be on the Lay homepage
        When I invite a Co-Deputy to the service who is already registered
        Then they shouldn't be able to register to deputise for a client with already registered details
        And they should see a 'deputy already linked to case number' error

    @super-admin
    Scenario: A Lay user entering an invalid reporting period
        Given a csv has been uploaded to the sirius bucket with the file 'lay-4-valid-rows.csv'
        When I run the lay CSV command the file contains 4 new pre-registration entities
        And a Lay Deputy registers to deputise for a client with valid details but invalid reporting period
        Then I should see an 'invalid reporting period' error
