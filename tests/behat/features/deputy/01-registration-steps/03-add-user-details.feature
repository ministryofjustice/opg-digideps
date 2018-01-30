Feature: deputy / user / add details

  @deputy
  Scenario: add user details (deputy)
    Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then I should be on "/user/details"
      # wrong form
    Then the following hidden fields should have the corresponding values:
      | user_details_firstname       | John      |
      | user_details_lastname        | Doe       |
      | user_details_addressPostcode | P0ST C0D3 |
    And the following fields should have the corresponding values:
      | user_details_address1        |           |
      | user_details_addressCountry  |           |
      | user_details_phoneMain       |           |
    And I press "user_details_save"
    Then the following fields should have an error:
      | user_details_address1        |
      | user_details_addressCountry  |
      | user_details_phoneMain       |
    And I press "user_details_save"
    Then the form should be invalid
      # test length validators
    When I fill in the following:
      | user_details_phoneMain       | 1234567890-1234567890 more than 20 chars |
    And I press "user_details_save"
    Then the following fields should have an error:
      | user_details_address1        |
      | user_details_addressCountry  |
      | user_details_phoneMain       |
    And I press "user_details_save"
    Then the form should be invalid
      # right values
    When I set the user details to:
      | address | 102 Petty France | MOJ           | London | PREFILLED | GB |
      | phone   | 020 3334 3555    | 020 1234 5678 |        |           |    |
    Then the form should be valid
    When I go to "/user/details"
    Then the following hidden fields should have the corresponding values:
      | user_details_firstname       | John      |
      | user_details_lastname        | Doe       |
      | user_details_addressPostcode | P0ST C0D3 |
    And the following fields should have the corresponding values:
      | user_details_address1         | 102 Petty France |
      | user_details_address2         | MOJ              |
      | user_details_address3         | London           |
      | user_details_addressCountry   | GB               |
      | user_details_phoneMain        | 020 3334 3555    |
      | user_details_phoneAlternative | 020 1234 5678    |

  @ndr
  Scenario: add user details (deputy ndr)
    Given I am logged in as "behat-user-ndr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    Then I should be on "/user/details"
    When I set the user details to:
      | name    | John NDR         | Doe NDR       |        |          |    |
      | address | 102 Petty France | MOJ           | London | p0stc0d3 | GB |
      | phone   | 020 3334 3555    | 020 1234 5678 |        |          |    |
    Then the form should be valid
    When I go to "/user/details"
    Then the following fields should have the corresponding values:
      | user_details_firstname        | John NDR         |
      | user_details_lastname         | Doe NDR          |
      | user_details_address1         | 102 Petty France |
      | user_details_address2         | MOJ              |
      | user_details_address3         | London           |
      | user_details_addressPostcode  | p0stc0d3         |
      | user_details_addressCountry   | GB               |
      | user_details_phoneMain        | 020 3334 3555    |
      | user_details_phoneAlternative | 020 1234 5678    |

    
        
