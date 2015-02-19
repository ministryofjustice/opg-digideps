Feature: add details
    
    Scenario: login and add user
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on "user/details"
        And I fill in the following:
            | user_details_firstname | John |
            | user_details_lastname | Doe |
            | user_details_address1 | 102 Petty France |
            | user_details_address2 | MOJ |
            | user_details_address3 | London |
            | user_details_addressPostcode | SW1H 9AJ |
            | user_details_addressCountry | uk |
            | user_details_phoneHome | 020 3334 3555  |
            | user_details_phoneWork | 020 1234 5678  |
            | user_details_phoneMobile | 079 123 456 78  |
        And I submit the form
        When I go to "user/details"
        Then the following fields should have the corresponding values:
            | user_details_firstname | John |
            | user_details_lastname | Doe |
            | user_details_address1 | 102 Petty France |
            | user_details_address2 | MOJ |
            | user_details_address3 | London |
            | user_details_addressPostcode | SW1H 9AJ |
            | user_details_addressCountry | uk |
            | user_details_phoneHome | 020 3334 3555  |
            | user_details_phoneWork | 020 1234 5678  |
            | user_details_phoneMobile | 079 123 456 78  |
            
        