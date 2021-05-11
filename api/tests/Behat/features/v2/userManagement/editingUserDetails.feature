Feature: Editing Deputy and Client details

  Scenario: Creating users to edit
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"

    Given the following users exist:
      | ndr      | deputyType | firstName | lastName    | email                 | postCode | activated |
      | disabled | LAY        | Hena      | Mercia      | hena.mercia@test.com  | HA4      | true      |

    Given the following clients exist and are attached to deputies:
      | firstName | lastName | phone       | address     | address2  | county  | postCode | caseNumber | deputyEmail            |
      | Jory      | Dunkeld  | 01215552222 | 1 Fake Road | Fakeville | Faketon | B4 6HQ   | JD123456   |  hena.mercia@test.com  |

  Scenario: edit client details
    And I am logged in as "hena.mercia@test.com" with password "DigidepsPass1234"
    And I click on "user-account, client-show, client-edit"
    Then the following fields should have the corresponding values:
      | client_firstname | Jory |
      | client_lastname | Dunkeld |
      | client_courtDate_day | 01 |
      | client_courtDate_month | 11 |
      | client_courtDate_year | 2017 |
      | client_address | 1 Fake Road |
      | client_address2 | Fakeville |
      | client_county | Faketon |
      | client_postcode | B4 6HQ |
      | client_country | GB |
      | client_phone | 01215552222  |
    When I fill in the following:
      | client_firstname | |
      | client_lastname |  |
      | client_courtDate_day | |
      | client_courtDate_month | |
      | client_courtDate_year | |
      | client_address |  |
      | client_address2 |  |
      | client_county | |
      | client_postcode | |
      | client_country | |
      | client_phone | aaa |
    And I press "client_save"
    Then the following fields should have an error:
      | client_firstname |
      | client_lastname |
      | client_courtDate_day |
      | client_courtDate_month |
      | client_courtDate_year |
      | client_address |
      | client_postcode |
      | client_phone |
    When I fill in the following:
      | client_firstname | Nolan |
      | client_lastname | Ross |
      | client_courtDate_day | 1 |
      | client_courtDate_month | 1 |
      | client_courtDate_year | 2016 |
      | client_address |  2 South Parade |
      | client_address2 | First Floor  |
      | client_county | Nottingham  |
      | client_postcode | NG1 2HT  |
      | client_country | GB |
      | client_phone | 0123456789  |
    And I press "client_save"
    Then the form should be valid
