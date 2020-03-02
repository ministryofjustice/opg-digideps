Feature: Editing Deputy and Client details

  Scenario: Creating users to edit
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"

    Given the following users exist:
      | ndr      | deputyType | firstName | lastName    | email                 | postCode | activated |
      | disabled | LAY        | Hena      | Mercia      | hena.mercia@test.com  | HA4      | true      |

    Given the following clients exist and are attached to deputies:
      | firstName | lastName | phone       | address     | address2  | county  | postCode | caseNumber | deputyEmail            |
      | Jory      | Dunkeld  | 01215552222 | 1 Fake Road | Fakeville | Faketon | B4 6HQ   | JD123456   |  hena.mercia@test.com  |

  Scenario: Editing deputy details
    Given I am logged in as "hena.mercia@test.com" with password "Abcd1234"
    And I click on "user-account, profile-show, profile-edit"
    Then I should be on "/deputyship-details/your-details/edit"
    And the following fields should have the corresponding values:
      | profile_firstname | Hena |
      | profile_lastname | Mercia |
      | profile_address1 | Victoria road |
      | profile_address2 |  |
      | profile_address3 |  |
      | profile_addressPostcode | HA4 |
      | profile_addressCountry | GB |
      | profile_phoneMain | 07911111111111 |
      | profile_phoneAlternative | |
      | profile_email | hena.mercia@test.com |
    When I fill in the following:
      | profile_firstname |  |
      | profile_lastname |  |
      | profile_address1 | |
      | profile_addressPostcode | |
      | profile_addressCountry | |
      | profile_phoneMain |   |
      | profile_email | |
    And I press "profile_save"
    Then the following fields should have an error:
      | profile_firstname |
      | profile_lastname |
      | profile_address1 |
      | profile_addressPostcode |
      | profile_addressCountry |
      | profile_phoneMain |
      | profile_email |
    When I press "profile_save"
    Then the form should be invalid
    When I fill in the following:
      | profile_firstname | Paul |
      | profile_lastname | Jamie |
      | profile_address1 | 103 Petty France |
      | profile_address2 | MOJDS |
      | profile_address3 | London |
      | profile_addressPostcode | SW1H 9AA |
      | profile_addressCountry | GB |
      | profile_phoneMain | 020 3334 3556  |
      | profile_phoneAlternative | 020 1234 5679  |
      | profile_email | hena.mercia@test.com |
    And I press "profile_save"
    Then the form should be valid

  Scenario: edit client details
    And I am logged in as "hena.mercia@test.com" with password "Abcd1234"
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
