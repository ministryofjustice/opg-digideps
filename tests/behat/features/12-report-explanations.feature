Feature: report explanations

    @deputy
    Scenario: add explanation for no contacts
      Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
      And I am on the first report overview page
      And I follow "tab-contacts"
      # empty form throws error
      When I fill in "reason_for_no_contact_reason" with ""
      And I press "reason_for_no_contact_saveReason"
      Then the form should contain an error
      # add reason
      When I fill in "reason_for_no_contact_reason" with "kept in the book"
      And I press "reason_for_no_contact_saveReason"
      Then the form should not contain an error
      And the following fields should have the corresponding values:
         | reason_for_no_contact_reason | kept in the book |

        
        
        



