@v2 @registration @self-register @v2_sequential
Feature: Lay Deputy Self Registration

    @super-admin
    Scenario: A Lay user entering an invalid case number cannot self register
        Given a super admin user accesses the admin app
        When I navigate to the upload users page
        And I upload a lay CSV that contains 3 new pre-registration entities
        And a Lay Deputy registers to deputise for a client with an invalid case number
        Then I should see an 'invalid case number' error

    @super-admin
    Scenario: A Lay user entering an valid case number and invalid case details cannot self register
        Given a super admin user accesses the admin app
        When I navigate to the upload users page
        And I upload a lay CSV that contains 3 new pre-registration entities
        And a Lay Deputy registers to deputise for a client with a valid case number and invalid case details
        And I should see an 'invalid deputy lastname' error
        And I should see an 'invalid deputy postcode' error
        Then I should see an 'invalid client lastname' error

    @super-admin
    Scenario: A Lay user with an existing pre-registration record can self register
        Given a super admin user accesses the admin app
        When I navigate to the upload users page
        And I upload a lay CSV that contains 3 new pre-registration entities
        And a Lay Deputy registers to deputise for a client with valid details
        Then my deputy details should be saved to my account
        And I should be on the Lay homepage

    @super-admin
    Scenario: A Lay user inputting unicode characters can self register
        Given a super admin user accesses the admin app
        When I navigate to the upload users page
        And I upload a lay CSV that contains a new pre-registration entity with special characters
        And a Lay Deputy registers with valid details using unicode characters
        Then my deputy details should be saved to my account
        And I should be on the Lay homepage

    @super-admin
    Scenario: A Lay user with an existing pre-registration record and a user account created by a case manager can register
        Given a super admin user accesses the admin app
        When I navigate to the upload users page
        And I upload a lay CSV that contains 3 new pre-registration entities
        And I create a Lay Deputy user account for one of the deputies in the CSV
        When a Lay Deputy clicks the activation link in the registration email
        And I complete the case manager user registration flow with valid deputyship details
        Then my deputy details should be saved to my account
        And I should be on the Lay homepage

    @super-admin
    Scenario: A Co-deputy can register for the service
        Given a super admin user accesses the admin app
        When I navigate to the upload users page
        And I upload a lay CSV that contains 2 new pre-registration entities for the same case
        And one of the Lay Deputies registers to deputise for a client with valid details
        Then my deputy details should be saved to my account
        And I should be on the Lay homepage
        When I invite a Co-Deputy to the service
        Then they should be able to register to deputise for a client with valid details
        And they should be on the Lay homepage
