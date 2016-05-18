Feature: Browser - manage contacts

    @browser
    Scenario: Add and delete reason for no contacts
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "edit-contacts"
        And I fill in the following:
            | reason_for_no_contact_reasonForNoContacts | nothing relevant contact added |
        Then I save the page as "contact-reason"
        And I press "reason_for_no_contact_save"
        And I should see "nothing relevant contact added" in the "reason-no-contacts" region
        When I click on "edit-reason-no-contacts, delete-button"
        And I save the page as "contact-reason-delete-confirm"
        Then I should see a confirmation
        When I click on "delete-confirm"
        Then the URL should match "/report/\d+/contacts"
        And the following fields should have the corresponding values:
            | reason_for_no_contact_reasonForNoContacts | |

    @browser
    Scenario: Add two contacts then delete one
        Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I follow "edit-contacts"
        When I follow "add-contacts-button"
        Then I save the page as "contact-add-1"
        Then I fill in the following:
            | contact_contactName | Andy Brown |
            | contact_relationship | brother |
            | contact_explanation | no explanation |
            | contact_address | 46 Noth Road |
            | contact_postcode | N2 5JF |
            | contact_country | GB |

        And I press "contact_save"
        And the URL should match "/report/\d+/contacts"
        When I follow "add-contacts-button"
        Then I fill in the following:
            | contact_contactName | Julie Brown |
            | contact_relationship | Sister |
            | contact_explanation | no explanation |
            | contact_address | 46 Noth Road |
            | contact_postcode | N2 5JF |
            | contact_country | GB |
        Then I save the page as "contact-add-2"
        And I press "contact_save"
        And the URL should match "/report/\d+/contacts"
        Then I save the page as "contact-list"
        Then I should see "Andy Brown" in the "list-contacts" region
        Then I should see "Julie Brown" in the "list-contacts" region
        Then I click on the first contact
        And I click on "delete-button"
        Then I save the page as "contact-delete-confirm"
        Then I should see a confirmation
        When I click on "delete-confirm"
        Then the URL should match "/report/\d+/contacts"
        Then I should not see "Andy Brown" in the "list-contacts" region
        Then I should see "Julie Brown" in the "list-contacts" region
        
