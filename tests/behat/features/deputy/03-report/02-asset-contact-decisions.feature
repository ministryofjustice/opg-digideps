Feature: deputy / report / add contact, decision, assets

    @deputy
    Scenario: add contact
        Given I load the application status from "report-empty"
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        #And I am on the "2015" report overview page
        And I follow "edit-contacts"
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
        And I add the following contacts:
            | contactName | relationship | explanation     | address       | address2 | county | postcode | country |
            | Andy White  |  GP          | I owe him money | 45 Noth Road | Islington | London | N2 5JF   | GB      |
        And I save the page as "report-contact-list"
        Then the response status code should be 200
        And the form should be valid
        And the URL should match "/report/\d+/contacts"
        And I should see "Andy White" in the "list-contacts" region


    @deputy
    Scenario: add decision
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        #And I am on the "2015" report overview page
        And I follow "edit-decisions"
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
        And I add the following decisions:
            | description | clientInvolved | clientInvolvedDetails |
            | 2 beds | yes | the client was able to decide at 90% |
            | 3 beds | yes | the client was able to decide at 85% |
       And I should see "2 beds" in the "list-decisions" region
       And I should see "3 beds" in the "list-decisions" region
       And I save the page as "report-decision-list"


    @deputy
    Scenario: add asset
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        #And I am on the "2015" report overview page
        And I follow "edit-assets"
        And I save the page as "report-assets-empty"
        # wrong form
        And I press "asset_title_next"
        And I save the page as "report-assets-title-add-error-empty"
        Then the following fields should have an error:
            | asset_title_title |
        Then I fill in "asset_title_title" with "Vehicles"
        And I press "asset_title_next"
        Then the form should be valid
        And I save the page as "report-assets-title-added"
        # rest of the form
        When I press "asset_save"
          Then the following fields should have an error:
            | asset_value |
            | asset_description |
        When I fill in the following:
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
        When I add the following assets:
          | title        | value       |  description        | valuationDate |
          | Property    | 250000.00   |  2 beds flat in HA2 |               |
          | Vehicles    | 13000.00   |  Alfa Romeo 156 JTD |    10/11/2015  |
        And I should see "2 beds flat in HA2" in the "list-assets" region
        And I should see "£250,000.00" in the "list-assets" region
        Then I should see "Alfa Romeo 156 JTD" in the "list-assets" region
        And I should see "£13,000.00" in the "list-assets" region
        And I save the page as "report-assets-list"

    @deputy
    Scenario: provide safeguarding info
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "edit-safeguarding"
        Then I fill in the following:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I press "safeguarding_save"
