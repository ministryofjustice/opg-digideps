Feature: deputy / report / contacts

    @deputy
    Scenario: add contact
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        # TODO implement me (OTPP)
#        And I click on "reports,report-2016-open, edit-contacts"
#        And I save the page as "report-contact-empty"
#        # wrong form
#        When I follow "add-contacts-button"
#        And I press "contact_save"
#        And I save the page as "report-contact-add-error"
#        Then the following fields should have an error:
#            | contact_contactName |
#            | contact_relationship |
#            | contact_explanation |
#        # right values
#        Then the "contact_explanation" field is expandable
#        And I add the following contacts:
#            | contactName | relationship | explanation     | address       | address2 | county | postcode | country |
#            | Andy White  |  GP          | I owe him money | 45 Noth Road | Islington | London | N2 5JF   | GB      |
#        And I save the page as "report-contact-list"
#        #Then the response status code should be 200
#        And the form should be valid
#        And the URL should match "/report/\d+/contacts"
#        And I should see "Andy White" in the "list-contacts" region
