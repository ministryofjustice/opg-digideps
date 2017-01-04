Feature: deputy / report / contacts

    @deputy
    Scenario: contacts
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        # TODO implement me (OTPP)
        And I click on "reports,report-2016-open, edit-contacts, start"
        # chose "no records"
        Given the step cannot be submitted without making a selection
        Then the step with the following values CANNOT be submitted:
            | contact_exist_hasContacts_1 | no |
        And the step with the following values CAN be submitted:
            | contact_exist_hasContacts_1 | no |
            | contact_exist_reasonForNoContacts | rfnc |
        # summary page check
        And each text should be present in the corresponding region:
            | No      | has-contacts       |
            | rfnc    | reason-no-contacts |
        # select there are records (from summary page link)
        Given I click on "edit" in the "has-contacts" region
        And the step with the following values CAN be submitted:
            | contact_exist_hasContacts_0 | yes |
        # add contact n.1 (and validate form)
        And the step cannot be submitted without making a selection
        And the step with the following values CAN be submitted:
            | contact_contactName | Andy White  |
            | contact_relationship | GP |
            | contact_explanation | I owe him money |
            |  contact_address| 45 Noth Road |
            | contact_address2 | Islington |
            | contact_county | London |
            | contact_postcode | N2 AW1 |
            | contact_country | GB |
        # add contact n.2
        And I choose "yes" when asked for adding another record
        And the step with the following values CAN be submitted:
            | contact_contactName | Peter Black  |
            | contact_relationship | friend |
            | contact_explanation | I owe him lots of money |
            |  contact_address| 45 Noth Road2 |
            | contact_address2 | Islington2 |
            | contact_county | London2 |
            | contact_postcode | SW1 PB1 |
            | contact_country | GB |
        # add another: no
        And I choose "no" when asked for adding another record
        # check record in summary page
        And each text should be present in the corresponding region:
            | Andy White   | contact-n2-aw1 |
            | GP | contact-n2-aw1 |
            | I owe him money | contact-n2-aw1 |
            | Peter Black   | contact-sw1-pb1 |
            | friend | contact-sw1-pb1 |
            | I owe him lots of money | contact-sw1-pb1 |
        # remove contact n.2
        When I click on "delete" in the "contact-sw1-pb1" region
        Then I should not see the "contact-sw1-pb1" region
        # test add link
        When I click on "add"
        Then I should see the "save-and-continue" link
        When I go back from the step
        # edit contact n.1
        When I click on "edit" in the "contact-n2-aw1" region
        Then the following fields should have the corresponding values:
            | contact_contactName | Andy White  |
            | contact_relationship | GP |
            | contact_explanation | I owe him money |
            |  contact_address| 45 Noth Road |
            | contact_address2 | Islington |
            | contact_county | London |
            | contact_postcode | N2 AW1 |
            | contact_country | GB |
        And the step with the following values CAN be submitted:
            | contact_contactName | Andy Whites  |
            | contact_relationship | my GP |
            | contact_explanation | he takes care of my health |
            |  contact_address| 46 Noth Road |
            | contact_address2 | Camden |
            | contact_county | Greater London  |
            | contact_postcode | N2 AW2 |
            | contact_country | FR |
        And each text should be present in the corresponding region:
            | Andy Whites   | contact-n2-aw2 |
            | 46 Noth Road | contact-n2-aw2 |
            | Greater London | contact-n2-aw2 |
            | France | contact-n2-aw2 |
            | my GP | contact-n2-aw2 |
            | he takes care of my health | contact-n2-aw2 |


