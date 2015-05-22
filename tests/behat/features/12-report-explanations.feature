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

    @deputy
    Scenario: add explanation for no decisions
      Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
      And I am on the first report overview page
      # delete current decision
      And I follow "tab-decisions"
      And I click on "decision-n1"
      And I click on "delete-confirm"
      And I click on "delete"
      # add explanation
      # empty form throws error
      When I fill in "reason_for_no_decision_reason" with ""
      And I press "reason_for_no_decision_saveReason"
      Then the form should contain an error
      # add reason
      When I fill in the following:
        | reason_for_no_decision_reason | small budget |  
      And I press "reason_for_no_decision_saveReason"
      Then the form should not contain an error
      And I should see "small budget" in the "reason-no-decisions" region
      # edit reason, and cancel
      When I click on "edit-reason-no-decisions"
      Then the following fields should have the corresponding values:
        | reason_for_no_decision_reason | small budget |  
      When I click on "cancel-edit-reason"
      Then the URL should match "/report/\d+/decisions"
      # edit reason, and save
      When I click on "edit-reason-no-decisions"
      And I fill in the following:
        | reason_for_no_decision_reason | nothing relevant purchased or sold |  
      And I press "reason_for_no_decision_saveReason"
      And I should see "nothing relevant purchased or sold" in the "reason-no-decisions" region
      # delete reason and cancel
      When I click on "edit-reason-no-decisions"
      And I click on "delete-confirm"
      And I click on "delete-reason-confirm-cancel"
      Then the URL should match "/report/\d+/decisions/edit-reason"
      # delete reason and confirm
      When I click on "delete-confirm"
      When I click on "delete-reason"
      Then the URL should match "/report/\d+/decisions"
      And the following fields should have the corresponding values:
        | reason_for_no_decision_reason | |  
      
      
      
      
      
        
        
        

