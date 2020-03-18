Feature: Lay deputy upload into registration lookup table
  In order to ensure that I can use the service
  As a lay deputy
  I need the details of my court order loading into the application to enable me to register

  Scenario: CSV upload populates registration lookup table and deletes entries corresponding to the CSV source
    Given the self registration lookup table is empty
    # Upload 25 users from a casrec source
    When an admin user uploads the "behat-lay-casrec.csv" file into the Lay CSV uploader
    Then I should see "25 users in the database"
    # Upload 10 users from a sirius source
    When an admin user uploads the "behat-lay-sirius.csv" file into the Lay CSV uploader
    Then I should see "35 users in the database"
    # Upload 10 users from a casrec source
    When an admin user uploads the "behat-lay-casrec-follow-up.csv" file into the Lay CSV uploader
    Then I should see "20 users in the database"
    # Upload 5 users from a sirius source
    When an admin user uploads the "behat-lay-sirius-follow-up.csv" file into the Lay CSV uploader
    Then I should see "15 users in the database"
