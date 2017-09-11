Feature: PA client profile

  Scenario: PA view client details
    Given I load the application status from "team-users-complete"
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000010" region
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
      | pa_client_edit_dateOfBirth_day   | 01            |
      | pa_client_edit_dateOfBirth_month | 01            |
      | pa_client_edit_dateOfBirth_year  | 1967          |
      | pa_client_edit_phone             | 078912345678  |
      | pa_client_edit_email             | cly1@hent.com |
      | pa_client_edit_address           | ADD1          |
      | pa_client_edit_address2          | ADD2          |
      | pa_client_edit_county            | ADD3          |
      | pa_client_edit_postcode          | B301QL        |
    # format errors
    When I fill in the following:
      | pa_client_edit_dateOfBirth_day   | 12                                                                                                                                                                                                                                                               |
      | pa_client_edit_dateOfBirth_month | 12                                                                                                                                                                                                                                                               |
      | pa_client_edit_dateOfBirth_year  | 2056                                                                                                                                                                                                                                                             |
      | pa_client_edit_phone             | 1234                                                                                                                                                                                                                                                             |
      | pa_client_edit_email             | invalidEmail                                                                                                                                                                                                                                                     |
      | pa_client_edit_address           | 01234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-01234567890 more than 200 chars |
      | pa_client_edit_address2          | 01234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-01234567890 more than 200 chars |
      | pa_client_edit_county            | 01234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890- more than 75 chars                                                                                                                                   |
      | pa_client_edit_postcode          | 01234567890-01234567890 more than 10 chars                                                                                                                                                                                                                       |
    And I press "pa_client_edit_save"
    Then the following fields should have an error:
      | pa_client_edit_dateOfBirth_day   |
      | pa_client_edit_dateOfBirth_month |
      | pa_client_edit_dateOfBirth_year  |
      | pa_client_edit_phone             |
      | pa_client_edit_email             |
      | pa_client_edit_address           |
      | pa_client_edit_address2          |
      | pa_client_edit_county            |
      | pa_client_edit_postcode          |
      # correct form
    When I fill in the following:
      | pa_client_edit_dateOfBirth_day   | 02                   |
      | pa_client_edit_dateOfBirth_month | 02                   |
      | pa_client_edit_dateOfBirth_year  | 1968                 |
      | pa_client_edit_phone             | 078912345678-edited  |
      | pa_client_edit_email             | cly1-edited@hent.com |
      | pa_client_edit_address           | ADD1-edited          |
      | pa_client_edit_address2          | ADD2-edited          |
      | pa_client_edit_county            | ADD3-edited          |
      | pa_client_edit_postcode          | B301QM               |
    And I press "pa_client_edit_save"
    Then the form should be valid
    # assert view page contains edited values
    Then each text should be present in the corresponding region:
      | 1968                 | client-profile-details |
      | 078912345678-edited  | client-profile-details |
      | cly1-edited@hent.com | client-profile-details |
      | B301QM               | client-profile-details |
