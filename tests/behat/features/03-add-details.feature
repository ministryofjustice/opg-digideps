Feature: add details
    
    Scenario: add user details
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I am on "user/details"
        # missing user_details_firstname
        When I fill in the following:
            | user_details_firstname |  |
            | user_details_lastname | Doe |
            | user_details_address1 | 102 Petty France |
            | user_details_address2 | MOJ |
            | user_details_address3 | London |
            | user_details_addressPostcode | SW1H 9AJ |
            | user_details_addressCountry |  |
            | user_details_phoneHome | 020 3334 3555  |
            | user_details_phoneWork | 020 1234 5678  |
            | user_details_phoneMobile | 079 123 456 78  |
        And I submit the form
        Then the form should contain an error
        # missing  user_details_lastname
        When I fill in the following:
            | user_details_firstname | John |
            | user_details_lastname |  |
            | user_details_address1 | 102 Petty France |
            | user_details_address2 | MOJ |
            | user_details_address3 | London |
            | user_details_addressPostcode | SW1H 9AJ |
            | user_details_addressCountry |  |
            | user_details_phoneHome | 020 3334 3555  |
            | user_details_phoneWork | 020 1234 5678  |
            | user_details_phoneMobile | 079 123 456 78  |
        And I submit the form
        Then the form should contain an error
        # missing  user_details_address1
        When I fill in the following:
            | user_details_firstname | John |
            | user_details_lastname | Doe |
            | user_details_address1 |  |
            | user_details_address2 | MOJ |
            | user_details_address3 | London |
            | user_details_addressPostcode | SW1H 9AJ |
            | user_details_addressCountry | GB |
            | user_details_phoneHome | 020 3334 3555  |
            | user_details_phoneWork | 020 1234 5678  |
            | user_details_phoneMobile | 079 123 456 78  |
        And I submit the form
        Then the form should contain an error
        #  missing  user_details_addressPostcode
        When I fill in the following:
            | user_details_firstname | John |
            | user_details_lastname | Doe |
            | user_details_address1 | 102 Petty France |
            | user_details_address2 | MOJ |
            | user_details_address3 | London |
            | user_details_addressPostcode |  |
            | user_details_addressCountry | GB |
            | user_details_phoneHome | 020 3334 3555  |
            | user_details_phoneWork | 020 1234 5678  |
            | user_details_phoneMobile | 079 123 456 78  |
        And I submit the form
        Then the form should contain an error
        # missing  user_details_addressCountry
        When I fill in the following:
            | user_details_firstname | John |
            | user_details_lastname | Doe |
            | user_details_address1 | 102 Petty France |
            | user_details_address2 | MOJ |
            | user_details_address3 | London |
            | user_details_addressPostcode | SW1H 9AJ |
            | user_details_addressCountry |  |
            | user_details_phoneHome | 020 3334 3555  |
            | user_details_phoneWork | 020 1234 5678  |
            | user_details_phoneMobile | 079 123 456 78  |
        And I submit the form
        Then the form should contain an error
        #  missing  user_details_phoneHome
        When I fill in the following:
            | user_details_firstname | John |
            | user_details_lastname | Doe |
            | user_details_address1 | 102 Petty France |
            | user_details_address2 | MOJ |
            | user_details_address3 | London |
            | user_details_addressPostcode | SW1H 9AJ |
            | user_details_addressCountry | GB |
            | user_details_phoneHome |   |
            | user_details_phoneWork | 020 1234 5678  |
            | user_details_phoneMobile | 079 123 456 78  |
        And I submit the form
        Then the form should contain an error
        # right values
        When I fill in the following:
            | user_details_firstname | John |
            | user_details_lastname | Doe |
            | user_details_address1 | 102 Petty France |
            | user_details_address2 | MOJ |
            | user_details_address3 | London |
            | user_details_addressPostcode | SW1H 9AJ |
            | user_details_addressCountry | GB |
            | user_details_phoneHome | 020 3334 3555  |
            | user_details_phoneWork | 020 1234 5678  |
            | user_details_phoneMobile | 079 123 456 78  |
        And I submit the form
        Then the form should not contain an error
        When I go to "user/details"
        Then the following fields should have the corresponding values:
            | user_details_firstname | John |
            | user_details_lastname | Doe |
            | user_details_address1 | 102 Petty France |
            | user_details_address2 | MOJ |
            | user_details_address3 | London |
            | user_details_addressPostcode | SW1H 9AJ |
            | user_details_addressCountry | GB |
            | user_details_phoneHome | 020 3334 3555  |
            | user_details_phoneWork | 020 1234 5678  |
            | user_details_phoneMobile | 079 123 456 78  |


    Scenario: add user details (admin user)
        Given I am logged in as "deputyshipservice@publicguardian.gsi.gov.uk" with password "test"
        When I click on "user details"
        And I fill in the following:
            | user_details_firstname | John admin |
            | user_details_lastname | Doe admin |
        And I submit the form
        When i click on "user details"
        Then the following fields should have the corresponding values:
            | user_details_firstname | John admin |
            | user_details_lastname | Doe admin |
        
        