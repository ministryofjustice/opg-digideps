Feature: deputy / report / edit user contact

    @deputy
    Scenario: edit and remove contacts
        Given I load the application status from "report-submit-pre"
#        And I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
#        And I click on "reports, report-2016, edit-contacts"
#        And I click on "contact-n1"
#        Then the following fields should have the corresponding values:
#            | contact_contactName | Andy White |
#            | contact_relationship | GP |
#            | contact_explanation | I owe him money |
#            | contact_address | 45 Noth Road |
#            | contact_postcode | N2 5JF |
#            | contact_country | GB |
#        And I click on "cancel-edit"
#        And the URL should match "/report/\d+/contacts"
#        And I click on "contact-n1"
#        When I fill in the following:
#            | contact_contactName |  |
#            | contact_relationship |  |
#            | contact_explanation |  |
#            | contact_address |  |
#            | contact_postcode |  |
#        And I press "contact_save"
#        Then the following fields should have an error:
#            | contact_contactName |
#            | contact_relationship |
#            | contact_explanation |
#        # edit contact
#        When I fill in the following:
#            | contact_contactName | Andy Brown |
#            | contact_relationship | brother |
#            | contact_explanation | no explanation |
#            | contact_address | 46 Noth Road |
#            | contact_postcode | N2 5JF |
#            | contact_country | GB |
#        And I press "contact_save"
#        And the URL should match "/report/\d+/contacts"
#        Then I should see "Andy Brown" in the "list-contacts" region
#        And I should see "46 Noth Road" in the "list-contacts" region
#        And I click on "contact-n1"
#        And I click on "delete-button"
#        #Then the response status code should be 200
#        And the URL should match "/report/\d+/contacts"
#        Then I should not see the "list-contacts" region
#
#    @deputy
#    Scenario: add explanation for no contacts
#      Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
#      And I click on "reports,report-2016"
#      #delete current contact
#      And I follow "edit-contacts"
#      And I save the page as "report-no-contact-empty"
#      # add explanation
#      # empty form throws error
#      When I fill in "reason_for_no_contact_reasonForNoContacts" with ""
#      And I press "reason_for_no_contact_save"
#      Then the form should be invalid
#      And I save the page as "report-no-contact-error"
#      # add reason
#      When I fill in "reason_for_no_contact_reasonForNoContacts" with "kept in the book"
#      And I press "reason_for_no_contact_save"
#      Then the form should be valid
#      And I should see "kept in the book" in the "reason-no-contacts" region
#      And I save the page as "report-no-contact-added"
#      # edit reason, and cancel
#      When I click on "edit-reason-no-contacts"
#      Then the following fields should have the corresponding values:
#        | reason_for_no_contact_reasonForNoContacts | kept in the book |
#      When I click on "cancel-reason-button"
#      Then the URL should match "/report/\d+/contacts"
#      # edit reason, and save
#      When I click on "edit-reason-no-contacts"
#      And I save the page as "report-no-contact-edit"
#      And I fill in the following:
#        | reason_for_no_contact_reasonForNoContacts | |
#      And I press "reason_for_no_contact_save"
#      Then the form should be invalid
#      And I save the page as "report-no-contact-error"
#      And I fill in the following:
#        | reason_for_no_contact_reasonForNoContacts | nothing relevant contact added |
#      And I press "reason_for_no_contact_save"
#      And I save the page as "report-no-contact-edit"
#      And I should see "nothing relevant contact added" in the "reason-no-contacts" region
#      # delete reason and cancel
#      When I click on "edit-reason-no-contacts"
#      When I click on "delete-button"
#      Then the URL should match "/report/\d+/contacts"
#      And the following fields should have the corresponding values:
#        | reason_for_no_contact_reasonForNoContacts | |
