Feature: deputy / user / add client and report

  @deputy
  Scenario: update client (client name/case number/postcode already set)
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then I should be on "client/add"
    # submit empty form and check errors
    Then the following hidden fields should have the corresponding values:
      | client_firstname  | Cly      |
      | client_lastname   | Hent     |
      | client_caseNumber | behat001 |
    When I press "client_save"
    Then the following fields should have an error:
      | client_courtDate_day   |
      | client_courtDate_month |
      | client_courtDate_year  |
      | client_address         |
      | client_postcode        |
      # subit invalid values and check errors
    When I fill in the following:
      | client_courtDate_day   | 99                                                                                                                                                                                                                                                               |
      | client_courtDate_month | aa                                                                                                                                                                                                                                                               |
      | client_courtDate_year  | 0986789                                                                                                                                                                                                                                                          |
      | client_address         | 01234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-01234567890 more than 200 chars |
      | client_address2        | 01234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-01234567890 more than 200 chars |
      | client_county          | 01234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-0123456789001234567890-01234567890-01234567890-01234567890-01234567890 more than 200 chars |
      | client_postcode        | 01234567890 more than 10 chars                                                                                                                                                                                                                                   |
      | client_phone           | 01234567890-01234567890 more than 20 chars                                                                                                                                                                                                                       |
    And I press "client_save"
    Then the following fields should have an error:
      | client_courtDate_day   |
      | client_courtDate_month |
      | client_courtDate_year  |
      | client_address         |
      | client_address2        |
      | client_county          |
      | client_postcode        |
      | client_phone           |
      # right values
    When I set the client details to:
      | courtDate  | 1              | 1           | 2016       |         |    |
      # only tick Property and Affairs
      # if  Personal Welfare  is re-enabled, select the other one, then de-comment next feature block (about changing COT)
      | address    | 1 South Parade | First Floor | Nottingham | NG1 2HT | GB |
      | phone      | 0123456789     |             |            |         |    |
    Then the URL should match "report/create/\d+"
    When I go to "client/add"
    Then the following hidden fields should have the corresponding values:
      | client_firstname  | Cly      |
      | client_lastname   | Hent     |
      | client_caseNumber | behat001 |
      | client_postcode   | NG1 2HT  |
    And the following fields should have the corresponding values:
      | client_courtDate_day   | 01             |
      | client_courtDate_month | 01             |
      | client_courtDate_year  | 2016           |
      | client_address         | 1 South Parade |
      | client_address2        | First Floor    |
      | client_county          | Nottingham     |
      | client_country         | GB             |
      | client_phone           | 0123456789     |

  @odr
  Scenario: add client (odr) with no casrec record
    Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then I should be on "client/add"
      # right values
    When I set the client details with:
      | name       | Cly           | Hent         |            |         |    |
      | caseNumber | behat001       |             |            |         |    |
      | courtDate  | 1              | 1           | 2016       |         |    |
      | address    | 1 South Parade | First Floor | Nottingham | NG1 2HT | GB |
      | phone      | 0123456789     |             |            |         |    |
    # No casrec entry
    And I press "client_save"
    Then the form should be invalid

  @odr
  Scenario: add client (odr) with no casrec record
    Given I add the following users to CASREC:
      | Case     | Surname       | Deputy No | Dep Surname  | Dep Postcode | Typeofrep |
      | behat001 | Hent          | D001      | Doe ODR      | p0stc0d3      | OPG102    |
    And I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then I should be on "client/add"
      # right values
    When I set the client details with:
      | name       | Cly           | Hent         |            |         |    |
      | caseNumber | behat001       |             |            |         |    |
      | courtDate  | 1              | 1           | 2016       |         |    |
      | address    | 1 South Parade | First Floor | Nottingham | NG1 2HT | GB |
      | phone      | 0123456789     |             |            |         |    |
    And I press "client_save"
    Then the form should be valid

  @deputy
  Scenario: create report
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then the URL should match "report/create/\d+"
      # missing D,M,Y
    When I fill in the following:
      | report_startDate_day   | 01   |
      | report_startDate_month | 03   |
      | report_startDate_year  | 2016 |
      | report_endDate_day     |      |
      | report_endDate_month   |      |
      | report_endDate_year    |      |
    And I press "report_save"
    Then the following fields should have an error:
      | report_endDate_day   |
      | report_endDate_month |
      | report_endDate_year  |
    And I press "report_save"
    Then the form should be invalid
      # invalid date
    When I fill in the following:
      | report_startDate_day   | 01   |
      | report_startDate_month | 03   |
      | report_startDate_year  | 2016 |
      | report_endDate_day     | 99   |
      | report_endDate_month   | 99   |
      | report_endDate_year    | 2016 |
    And I press "report_save"
    Then the form should be invalid
      # date before report
    When I fill in the following:
      | report_startDate_day   | 01   |
      | report_startDate_month | 03   |
      | report_startDate_year  | 2016 |
      | report_endDate_day     | 31   |
      | report_endDate_month   | 12   |
      | report_endDate_year    | 2010 |
    And I press "report_save"
    Then the form should be invalid
      # date range too high
    When I fill in the following:
      | report_startDate_day   | 01   |
      | report_startDate_month | 03   |
      | report_startDate_year  | 2016 |
      | report_endDate_day     | 31   |
      | report_endDate_month   | 12   |
      | report_endDate_year    | 2020 |
    And I press "report_save"
    Then the form should be invalid
      # valid form
    Then I fill in the following:
      | report_startDate_day   | 01   |
      | report_startDate_month | 03   |
      | report_startDate_year  | 2016 |
      | report_endDate_day     | 31   |
      | report_endDate_month   | 12   |
      | report_endDate_year    | 2016 |
    And I press "report_save"
    Then the URL should match "/lay"

  @deputy
  Scenario: report-overview
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Given I click on "report-start"
    Then the URL should match "report/\d+/overview"

  @deputy
  Scenario: report-overview links
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    #Lay deputy links
    Then I should see the "user-account" link
    And I should see the "reports" link
    And I should see the "logout" link
    #PA links
    And I should not see the "pa-dashboard" link
    And I should not see the "pa-settings" link
