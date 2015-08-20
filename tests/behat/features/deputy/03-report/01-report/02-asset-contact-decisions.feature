Feature: report
    
    @deputy
    Scenario: add contact
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        #And I am on the first report overview page
        And I follow "tab-contacts"
        And I save the page as "report-contact-empty"
        # wrong form
        And I press "contact_save"
        And I save the page as "report-contact-add-error"
        Then the following fields should have an error:
            | contact_contactName |
            | contact_relationship |
            | contact_explanation |
            | contact_address |
            | contact_postcode |
        # right values
        Then the "contact_explanation" field is expandable
        And I add the following contact:
            | contactName | Andy White |
            | relationship | GP  |
            | explanation | I owe him money |
            | address | 45 Noth Road | Islington | London | N2 5JF | GB |
        And I save the page as "report-contact-list"
        Then the response status code should be 200
        And the form should be valid
        And the URL should match "/report/\d+/contacts"
        And I should see "Andy White" in the "list-contacts" region


    @deputy
    Scenario: add decision
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        #And I am on the first report overview page
        And I follow "tab-decisions"
        And I save the page as "report-decision-empty"
        # form errors
        When I press "decision_save"
        And I save the page as "report-decision-add-error"
        Then the following fields should have an error:
            | decision_description |
            | decision_clientInvolvedDetails |
            | decision_clientInvolvedBoolean_0 |
            | decision_clientInvolvedBoolean_1 |
        # missing involvement details
        And I fill in the following:
            | decision_description | 2 beds |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails |  |
        And I press "decision_save"
        And the form should be invalid
        # add decision 
        Then the "decision_description" field is expandable
        And the "decision_clientInvolvedDetails" field is expandable
        And I add the following decision:
            | description | 2 beds |
            | clientInvolved | yes | the client was able to decide at 90% |
       And I save the page as "report-decision-list"
       And I add the following decision:
            | description | 3 beds |
            | clientInvolved | yes | the client was able to decide at 85% |
       And I should see "2 beds" in the "list-decisions" region
       And I should see "3 beds" in the "list-decisions" region

        
    @deputy
    Scenario: add asset
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        #And I am on the first report overview page
        And I follow "tab-assets"
        And I save the page as "report-assets-empty"
        # wrong form
        And I press "asset_save"
        And I save the page as "report-assets-add-error-empty"
        Then the following fields should have an error:
            | asset_title |
            | asset_value |
            | asset_description |
        # invalid date
        When I fill in the following:
            | asset_title       | Vehicles | 
            | asset_value       | 10000000001 | 
            | asset_description | Alfa Romeo 156 JTD | 
            | asset_valuationDate_day | 99 | 
            | asset_valuationDate_month |  | 
            | asset_valuationDate_year | 2015 | 
        And I press "asset_save"
        And I save the page as "report-assets-add-error-date"
        Then the following fields should have an error:
            | asset_value |
            | asset_valuationDate_day |
            | asset_valuationDate_month |
            | asset_valuationDate_year |
        # first asset (empty date)
        Then the "asset_description" field should be expandable
        When I fill in the following:
            | asset_title       | Property | 
            | asset_value       | 250000.00 | 
            | asset_description | 2 beds flat in HA2 | 
            | asset_valuationDate_day |  | 
            | asset_valuationDate_month |  | 
            | asset_valuationDate_year |  | 
        And I press "asset_save"
        And I save the page as "report-assets-list-one"
        Then the response status code should be 200
        And the form should be valid
        And I should see "2 beds flat in HA2" in the "list-assets" region
        And I should see "£250,000.00" in the "list-assets" region
        When I click on "add-an-asset"
        # 2nd asset (with date)
        And I fill in the following:
            | asset_title       | Vehicles | 
            | asset_value       | 13000.00 | 
            | asset_description | Alfa Romeo 156 JTD | 
            | asset_valuationDate_day | 10 | 
            | asset_valuationDate_month | 11 | 
            | asset_valuationDate_year | 2015 | 
        And I press "asset_save"
        And I save the page as "report-assets-list-two"
        Then I should see "Alfa Romeo 156 JTD" in the "list-assets" region
        And I should see "£13,000.00" in the "list-assets" region