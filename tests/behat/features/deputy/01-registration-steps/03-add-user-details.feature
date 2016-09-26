Feature: deputy / user / add details
    
    @deputy
    Scenario: add user details (deputy) 
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I should be on "/user/details"
        And I save the page as "deputy-step2"
        # wrong form
        When I fill in the following:
            | user_details_firstname |  |
            | user_details_lastname |  |
        And I press "user_details_save"
        Then the following fields should have an error:
            | user_details_firstname |
            | user_details_lastname |
            | user_details_address1 |
            | user_details_addressPostcode |
            | user_details_addressCountry |
            | user_details_phoneMain |
        And I press "user_details_save"
        Then the form should be invalid
        And I save the page as "deputy-step2-empty-error"
        # test length validators
        When I fill in the following:
            | user_details_addressPostcode | 1234567890 more than 10 chars |
            | user_details_phoneMain | 1234567890-1234567890 more than 20 chars |
        And I press "user_details_save"
        Then the following fields should have an error:
            | user_details_firstname |
            | user_details_lastname |
            | user_details_address1 |
            | user_details_addressPostcode |
            | user_details_addressCountry |
            | user_details_phoneMain |
        And I press "user_details_save"
        Then the form should be invalid
        And I save the page as "deputy-step2-error"
        # right values
        When I set the user details to:
          | name | John | Doe | | | |
          | address | 102 Petty France | MOJ | London | SW1H 9AJ | GB |
          | phone | 020 3334 3555  | 020 1234 5678  | | | |
        Then the form should be valid
        When I go to "/user/details"
        Then the following fields should have the corresponding values:
            | user_details_firstname | John |
            | user_details_lastname | Doe |
            | user_details_address1 | 102 Petty France |
            | user_details_address2 | MOJ |
            | user_details_address3 | London |
            | user_details_addressPostcode | SW1H 9AJ |
            | user_details_addressCountry | GB |
            | user_details_phoneMain | 020 3334 3555  |
            | user_details_phoneAlternative | 020 1234 5678  |

    @odr
    Scenario: add user details (deputy odr)
        Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I should be on "/user/details"
        And I save the page as "odr-deputy-step2"
        When I set the user details to:
            | name | John ODR | Doe ODR | | | |
            | address | 102 Petty France | MOJ | London | SW1H 9AJ | GB |
            | phone | 020 3334 3555  | 020 1234 5678  | | | |
        Then the form should be valid
        When I go to "/user/details"
        Then the following fields should have the corresponding values:
            | user_details_firstname | John ODR |
            | user_details_lastname | Doe ODR |
            | user_details_address1 | 102 Petty France |
            | user_details_address2 | MOJ |
            | user_details_address3 | London |
            | user_details_addressPostcode | SW1H 9AJ |
            | user_details_addressCountry | GB |
            | user_details_phoneMain | 020 3334 3555  |
            | user_details_phoneAlternative | 020 1234 5678  |

    
        
        