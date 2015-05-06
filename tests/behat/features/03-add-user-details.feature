Feature: add details
    
    @deputy
    Scenario: add user details (deputy) 
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I should be on "user/details"
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
        Then the form should contain an error
        And I save the page as "deputy-step2-error"
        # right values
        When I fill in the following:
            | user_details_firstname | John |
            | user_details_lastname | Doe |
            | user_details_address1 | 102 Petty France |
            | user_details_address2 | MOJ |
            | user_details_address3 | London |
            | user_details_addressPostcode | SW1H 9AJ |
            | user_details_addressCountry | GB |
            | user_details_phoneMain | 020 3334 3555  |
            | user_details_phoneAlternative | 020 1234 5678  |
        And I press "user_details_save"
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
            | user_details_phoneMain | 020 3334 3555  |
            | user_details_phoneAlternative | 020 1234 5678  |
        

    @admin
    Scenario: add user details (admin user)
        Given I am logged in as "behat-admin-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I go to "user/details"
        And I save the page as "admin-step2"
        # testing validation, as the validation group for the form is different for admin user
        # missing firstname
        And I fill in the following:
            | user_details_firstname |  |
            | user_details_lastname | Doe admin |
        And I press "user_details_save"
        Then the form should contain an error
        # missing lastname
        And I fill in the following:
            | user_details_firstname | John admin |
            | user_details_lastname |  |
        And I press "user_details_save"
        Then the form should contain an error
        And I save the page as "admin-step2-error"
        # correct
        And I fill in the following:
            | user_details_firstname | John admin |
            | user_details_lastname | Doe admin |
        And I press "user_details_save"
        Then the form should not contain an error
        When I go to "user/details"
        Then the following fields should have the corresponding values:
            | user_details_firstname | John admin |
            | user_details_lastname | Doe admin |
        
        