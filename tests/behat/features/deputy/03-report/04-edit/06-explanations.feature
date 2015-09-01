Feature: deputy / report / edit user explanations


    Scenario: add explanation for no contacts
      Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
      And I click on "client-home"
      And I click on "report-n2"
      #delete current contact
      And I follow "tab-contacts"
      And I save the page as "report-no-contact-empty"
      # add explanation
      Then the "reason_for_no_contact_reason" field is expandable
      # empty form throws error
      When I fill in "reason_for_no_contact_reason" with ""
      And I press "reason_for_no_contact_saveReason"
      Then the form should be invalid
      And I save the page as "report-no-contact-error"
      # add reason
      When I fill in "reason_for_no_contact_reason" with "kept in the book"
      And I press "reason_for_no_contact_saveReason"
      Then the form should be valid
      And I should see "kept in the book" in the "reason-no-contacts" region
      And I save the page as "report-no-contact-added"
      # edit reason, and cancel
      When I click on "edit-reason-no-contacts"
      Then the following fields should have the corresponding values:
        | reason_for_no_contact_reason | kept in the book |
      When I click on "cancel-edit-reason"
      Then the URL should match "/report/\d+/contacts"
      # edit reason, and save
      When I click on "edit-reason-no-contacts"
      And I save the page as "report-no-contact-edit"
      And I fill in the following:
        | reason_for_no_contact_reason | |
      And I press "reason_for_no_contact_saveReason"
      Then the form should be invalid
      And I save the page as "report-no-contact-error"
      And I fill in the following:
        | reason_for_no_contact_reason | nothing relevant contact added |
      And I press "reason_for_no_contact_saveReason"
      And I save the page as "report-no-contact-edit"
      And I should see "nothing relevant contact added" in the "reason-no-contacts" region
      # delete reason and cancel
      When I click on "edit-reason-no-contacts"
      And I click on "delete-confirm"
      And I click on "delete-reason-confirm-cancel"
      Then the URL should match "/report/\d+/contacts/edit-reason"
      # delete reason and confirm
      When I click on "delete-confirm"
      When I click on "delete-reason"
      Then the URL should match "/report/\d+/contacts"
      And the following fields should have the corresponding values:
        | reason_for_no_contact_reason | |

    Scenario: add explanation for no decisions
      Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
      #And I am on the first report overview page
      And I click on "client-home"
      And I click on "report-n2"
      # delete current decision
      And I follow "tab-decisions"
      And I click on "decision-3-beds"
      And I click on "delete-confirm"
      And I click on "delete"
      And I save the page as "report-no-decision-empty"
      # add explanation
      Then the reason_for_no_decision_reason field is expandable
      # empty form throws error
      When I fill in "reason_for_no_decision_reason" with ""
      And I press "reason_for_no_decision_saveReason"
      Then the form should be invalid
      And I save the page as "report-no-decision-error"
      # add reason
      When I fill in the following:
        | reason_for_no_decision_reason | small budget |
      And I press "reason_for_no_decision_saveReason"
      Then the form should be valid
      And I should see "small budget" in the "reason-no-decisions" region
      And I save the page as "report-no-decision-added"
      # edit reason, and cancel
      When I click on "edit-reason-no-decisions"
      Then the following fields should have the corresponding values:
        | reason_for_no_decision_reason | small budget |
      When I click on "cancel-edit-reason"
      Then the URL should match "/report/\d+/decisions"
      # edit reason, and save
       When I click on "edit-reason-no-decisions"
      And I save the page as "report-no-decision-edit"
      And I fill in the following:
        | reason_for_no_decision_reason ||
      And I press "reason_for_no_decision_saveReason"
      Then the form should be invalid
      And I save the page as "report-no-decision-error"
      And I fill in the following:
        | reason_for_no_decision_reason | nothing relevant purchased or sold |
      And I press "reason_for_no_decision_saveReason"
      And I save the page as "report-no-decision-edit"
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
      
    Scenario: add explanation for no assets
      Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
      #And I am on the first report overview page
      And I click on "client-home"
      And I click on "report-n2"
      # delete current asset
      And I follow "edit-assets"
      And I click on "asset-2-beds-flat-in-ha2"
      And I click on "delete-confirm"
      And I click on "delete"
      Then the checkbox "report_no_assets_no_assets" should be unchecked
      And I save the page as "report-no-asset-empty"
      # submit without ticking the box
      And I press "report_no_assets_saveNoAsset"
      Then the form should be invalid
      And I save the page as "report-no-asset-error"
      # tick and submit
      When I check "report_no_assets_no_assets"
      And I press "report_no_assets_saveNoAsset"
      Then the form should be valid
      And I save the page as "report-no-asset-added"
      And I should see the "no-assets-selected" region
      # add asset 
      When I add the following assets:
        | title        | value       |  description        | valuationDate | 
        | Vehicles    | 13000.00    |  Alfa Romeo 156 JTD | 10/11/2015 |
      And I follow "tab-assets"
      And I click on "asset-alfa-romeo-156-jtd"
      And I click on "delete-confirm"
      And I click on "delete"
      # check checkbox is reset
      Then the checkbox "report_no_assets_no_assets" should be unchecked
