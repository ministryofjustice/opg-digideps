Feature: Self registration
  In order to ensure that I can use the service
  As a lay deputy
  I need to self register my details to create an account

  Background:
    Given the self registration lookup table is empty

  Scenario: Register a deputy from a casrec source
    Given an admin user uploads the "behat-lay-casrec.csv" file into the Lay CSV uploader
    When these deputies register to deputise the following court orders:
      | deputySurname | deputyEmail      | deputyPostCode | clientSurname | caseNumber |
      | Parker        | peter@parker.com | SW10 1AA       | Davies        | T5001034   |
    And I am logged in as "peter@parker.com" with password "DigidepsPass1234"
    Then I should be on "/lay"
    And I should see "Davies"

  Scenario: Register a deputy from a sirius source
    Given an admin user uploads the "behat-lay-sirius.csv" file into the Lay CSV uploader
    When these deputies register to deputise the following court orders:
      | deputySurname | deputyEmail    | deputyPostCode | clientSurname | caseNumber |
      | Wayne        | bruce@wayne.com | SW1 2SA        | Hendry        | T5001041   |
    And I am logged in as "bruce@wayne.com" with password "DigidepsPass1234"
    Then I should be on "/lay"
    And I should see "Hendry"
