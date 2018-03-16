Feature: PA client profile

  Scenario: PA view client details
    Given I load the application status from "team-users-complete"
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-01000010" region
    Then each text should be present in the corresponding region:
      | Cly1 Hent1    | client-profile-details |
      | 1967          | client-profile-details |
      | 078912345678  | client-profile-details |
      | cly1@hent.com | client-profile-details |
      | B301QL        | client-profile-details |
    # edit
    When I click on "client-edit"
      # submit empty form and check errors
    Then the following fields should have the corresponding values:
      | org_client_edit_dateOfBirth_day   | 01            |
      | org_client_edit_dateOfBirth_month | 01            |
      | org_client_edit_dateOfBirth_year  | 1967          |
      | org_client_edit_phone             | 078912345678  |
      | org_client_edit_email             | cly1@hent.com |
      | org_client_edit_address           | ADD1          |
      | org_client_edit_address2          | ADD2          |
      | org_client_edit_county            | ADD3          |
      | org_client_edit_postcode          | B301QL        |
    # format errors
    When I fill in the following:
      | org_client_edit_dateOfBirth_day   | 12                                                                                                                                                                                                                                                               |
      | org_client_edit_dateOfBirth_month | 12                                                                                                                                                                                                                                                               |
      | org_client_edit_dateOfBirth_year  | 2056                                                                                                                                                                                                                                                             |
      | org_client_edit_phone             | 1234                                                                                                                                                                                                                                                             |
      | org_client_edit_email             | invalidEmail                                                                                                                                                                                                                                                     |
      | org_client_edit_address           | 01234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-01234567890 more than 200 chars |
      | org_client_edit_address2          | 01234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-01234567890 more than 200 chars |
      | org_client_edit_county            | 01234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890- more than 75 chars                                                                                                                                   |
      | org_client_edit_postcode          | 01234567890-01234567890 more than 10 chars                                                                                                                                                                                                                       |
    And I press "pa_client_edit_save"
    Then the following fields should have an error:
      | org_client_edit_dateOfBirth_day   |
      | org_client_edit_dateOfBirth_month |
      | org_client_edit_dateOfBirth_year  |
      | org_client_edit_phone             |
      | org_client_edit_email             |
      | org_client_edit_address           |
      | org_client_edit_address2          |
      | org_client_edit_county            |
      | org_client_edit_postcode          |
      # correct form
    When I fill in the following:
      | org_client_edit_dateOfBirth_day   | 02                   |
      | org_client_edit_dateOfBirth_month | 02                   |
      | org_client_edit_dateOfBirth_year  | 1968                 |
      | org_client_edit_phone             | 078912345678-edited  |
      | org_client_edit_email             | cly1-edited@hent.com |
      | org_client_edit_address           | ADD1-edited          |
      | org_client_edit_address2          | ADD2-edited          |
      | org_client_edit_county            | ADD3-edited          |
      | org_client_edit_postcode          | B301QM               |
    And I press "pa_client_edit_save"
    Then the form should be valid
    # assert view page contains edited values
    Then each text should be present in the corresponding region:
      | 1968                 | client-profile-details |
      | 078912345678-edited  | client-profile-details |
      | cly1-edited@hent.com | client-profile-details |
      | B301QM               | client-profile-details |
