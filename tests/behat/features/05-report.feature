Feature: report
    
    @deputy
    Scenario: add contact
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I go to "/report/1/contacts/add"
        # missing name
        And I fill in the following:
            | contact_contactName |  |
            | contact_relationship | brother  |
            | contact_explanation | no explanation |
            | contact_address | 45 Noth Road |
            | contact_address2 | Inslington |
            | contact_county | London |
            | contact_postcode | N2 5JF |
            | contact_country | GB |
        And I submit the form
        And the form should contain an error
        # missing relationship
        And I fill in the following:
            | contact_contactName | Andy White |
            | contact_relationship |   |
            | contact_explanation | no explanation |
            | contact_address | 45 Noth Road |
            | contact_address2 | Inslington |
            | contact_county | London |
            | contact_postcode | N2 5JF |
            | contact_country | GB |
        And I submit the form
        And the form should contain an error
        # missing explanation
        And I fill in the following:
            | contact_contactName | Andy White |
            | contact_relationship | brother  |
            | contact_explanation |  |
            | contact_address | 45 Noth Road |
            | contact_address2 | Inslington |
            | contact_county | London |
            | contact_postcode | N2 5JF |
            | contact_country | GB |
        And I submit the form
        And the form should contain an error
        # missing address
        And I fill in the following:
            | contact_contactName | Andy White |
            | contact_relationship | brother  |
            | contact_explanation | no explanation |
            | contact_address |  |
            | contact_address2 | Inslington |
            | contact_county | London |
            | contact_postcode | N2 5JF |
            | contact_country | GB |
        And I submit the form
        And the form should contain an error
        # missing postcode
        And I fill in the following:
            | contact_contactName | Andy White |
            | contact_relationship | brother  |
            | contact_explanation | no explanation |
            | contact_address |  |
            | contact_address2 | Inslington |
            | contact_county | London |
            | contact_postcode |  |
            | contact_country | GB |
        And I submit the form
        And the form should contain an error
        # right values
        And I fill in the following:
            | contact_contactName | Andy White |
            | contact_relationship | brother  |
            | contact_explanation | no explanation |
            | contact_address | 45 Noth Road |
            | contact_address2 | Inslington |
            | contact_county | London |
            | contact_postcode | N2 5JF |
            | contact_country | GB |
        And I submit the form
        Then the response status code should be 200
        And the form should not contain an error
        And I should be on "/report/1/contacts"
        And I should see "Andy White" in the "list-contacts" region


    @deputy
    Scenario: add decision
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        When I go to "/report/1/decisions/add"
        # missing title
        And I fill in the following:
            | decision_title |  |
            | decision_description | 2 beds |
            | decision_decisionDate_day | 30 |
            | decision_decisionDate_month | 12 |
            | decision_decisionDate_year | 2015 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 90% |
        And I submit the form
        And the form should contain an error
         # missing description
        And I fill in the following:
            | decision_title | Bought house in Sw18 |
            | decision_description |  |
            | decision_decisionDate_day | 30 |
            | decision_decisionDate_month | 12 |
            | decision_decisionDate_year | 2015 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 90% |
        And I submit the form
        And the form should contain an error
        # missing date
        And I fill in the following:
            | decision_title | Bought house in Sw18 |
            | decision_description | 2 beds |
            | decision_decisionDate_day |  |
            | decision_decisionDate_month |  |
            | decision_decisionDate_year |  |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 90% |
        And I submit the form
        And the form should contain an error
        # wrong date
        And I fill in the following:
            | decision_title | Bought house in Sw18 |
            | decision_description | 2 beds |
            | decision_decisionDate_day | 30 |
            | decision_decisionDate_month | 02 |
            | decision_decisionDate_year | 9999 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 90% |
        And I submit the form
        And the form should contain an error
        # date not in report range
        And I fill in the following:
            | decision_title | Bought house in Sw18 |
            | decision_description | 2 beds |
            | decision_decisionDate_day | 30 |
            | decision_decisionDate_month | 12 |
            | decision_decisionDate_year | 2002 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 90% |
        And I submit the form
        And the form should contain an error
        # missing involvement details
        And I fill in the following:
            | decision_title | Bought house in Sw18 |
            | decision_description | 2 beds |
            | decision_decisionDate_day | 30 |
            | decision_decisionDate_month | 12 |
            | decision_decisionDate_year | 2015 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails |  |
        And I submit the form
        And the form should contain an error
        # right values
        And I fill in the following:
            | decision_title | Bought house in Sw18 |
            | decision_description | 2 beds |
            | decision_decisionDate_day | 30 |
            | decision_decisionDate_month | 12 |
            | decision_decisionDate_year | 2015 |
            | decision_clientInvolvedBoolean_0 | 1 |
            | decision_clientInvolvedDetails | the client was able to decide at 90% |
        And I submit the form
        Then the response status code should be 200
        And the form should not contain an error
        And I should be on "/report/1/decisions"
        And I should see "Bought house in Sw18" in the "decision" region

    