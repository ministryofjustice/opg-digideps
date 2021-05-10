Feature: Manually discharge deputies from a court order
  In order to allow clients to be deputised by a new deputy
  As a super admin user
  I need to discharge the deputy from the court order

  ##
  # Manual discharging:
  # We can not auto detect when a client has switched between lay deputy or between a lay and org deputyship, or vice versa
  # We have an admin option to "discharge" a deputy from a client, which effectively deletes the client, allowing for a new one to be created
  Scenario: Super admin user manually discharges a lay deputy from their only client
    Given the following court orders exist:
      | client   | deputy    | deputy_type | report_type                                | court_date |
      | 32163425 | Deputy432 | LAY         | Property and Financial Affairs High Assets | 2018-01-30 |
    When a super admin discharges the deputy from "32163425"
    And I am logged in as "deputy432@behat-test.com" with password "DigidepsPass1234"
    Then I should be on "/client/add"

  Scenario: Super admin user manually discharges an org based named deputy from their client
    Given the following court orders exist:
      | client   | deputy    | deputy_type | report_type        | court_date |
      | 43853417 | Deputy043 | PROF        | Health and Welfare | 2018-01-30 |
      | 43853418 | Deputy043 | PROF        | Health and Welfare | 2018-01-31 |
    When a super admin discharges the deputy from "43853417"
    And I am logged in as "deputy043@behat-test.com" with password "DigidepsPass1234"
    Then I should see "43853418"
    And I should not see "43853417"

  @acl
  Scenario: Non super admin user cannot discharge
    Given the following court orders exist:
      | client   | deputy    | deputy_type | report_type        | court_date |
      | 84775409 | Deputy329 | PA          | Health and Welfare | 2017-03-30 |
    When I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I visit the client page for "84775409"
    Then I should not see "Discharge deputy"
