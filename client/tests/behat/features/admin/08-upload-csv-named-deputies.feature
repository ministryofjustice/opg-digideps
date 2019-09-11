Feature: admin uploads csv organisations

 @prof
   Scenario: CSV upload creates organisation and Named Deputy against client
      Given emails are sent from "admin" area
       And I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
         # upload PROF users
       When I click on "admin-upload-pa"
       When I attach the file "behat-prof.csv" to "admin_upload_file"
       And I press "admin_upload_upload"
       Then the form should be valid
       Then I should see "Added 0 Prof users, 0 PA users, 3 clients, 3 named deputies and 3 reports."
       And I click on "admin-client-search, client-detail-34350001"
       Then I should see "NAMED_FN NAMED_SN" in the "nd-name" region
       And each text should be present in the corresponding region:
        | NAMED_ADD1 | nd-address |
        | NAMED_ADD2 | nd-address |
        | NAMED_ADD3 | nd-address |
        | NAMED_ADD4 | nd-address |
        | NAMED_ADD5 | nd-address |
        | email2@dd-professionals.co.uk | nd-contact-details |
        | email3@dd-professionals.co.uk | nd-contact-details |

    Scenario: Add Prof user
      Given emails are sent from "admin" area
      And I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
      Then I go to admin page "/admin"
      And I create a new "NDR-disabled" "Prof named" user "Prof Main" "Contact" with email "admin@dd-professionals.co.uk" and postcode "SW19"
      Then I am on admin page "/admin/organisations"
      And I should see "behat-orgprof1@dd-professionals.co.uk" in the "org-behat-orgprof1dd-professionalscouk" region
      And I should see "*@dd-professionals.co.uk" in the "org-email-identifier-behat-orgprof1dd-professionalscouk" region
      And I should see "" in the "org-active-behat-orgprof1dd-professionalscouk" region
      And I click on "manage" in the "org-behat-orgprof1dd-professionalscouk" region
      Then I should see the "org-client" region exactly "2" times
      And I should see "This organisation doesn't have any members" in the "org-members-list" region
      And I follow "Add someone to this organisation"
      When I fill in "organisation_add_user_email" with "admin@dd-professionals.co.uk"
      And I press "Find user"
      Then each text should be present in the corresponding region:
        | Prof Main Contact                 | org-found-user-fullname |
        | admin@dd-professionals.co.uk      | org-found-user-email |
        | No                                | org-found-user-active |
      Then I click on "organisation_add_user_confirm"
      And the response status code should be 200
      And I should see "Prof Main Contact has been added to behat-orgprof1@dd-professionals.co.uk" in the "alert-message" region
      And I should see the "org-members-list" region exactly "1" times
      And I should see "PROF Main Contact"
      And I should see "admin@dd-professionals.co.uk"


  Scenario: Activate organisation
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I am on admin page "/admin/organisations"
    And I should see "" in the "org-active-behat-orgprof1dd-professionalscouk" region
    And I click on "edit" in the "org-behat-orgprof1dd-professionalscouk" region
    And I fill in "organisation_isActivated_0" with "1"
    And I press "Save organisation"
   Then I should see "The organisation has been updated" in the "alert-message" region
    Then I should be on "/admin/organisations/"
    And each text should be present in the corresponding region:
      | behat-orgprof1@dd-professionals.co.uk | org-behat-orgprof1dd-professionalscouk |
      | *@dd-professionals.co.uk              | org-behat-orgprof1dd-professionalscouk |
      | Active                                | org-behat-orgprof1dd-professionalscouk |

  Scenario: Activate Prof user
    Given emails are sent from "admin" area
    And I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    And I click on "send-activation-email" in the "user-admindd-professionalscouk" region
    Then the response status code should be 200
    And the last email containing a link matching "/user/activate/" should have been sent to "admin@dd-professionals.co.uk"

  Scenario: PROF user registration steps
    Given emails are sent from "admin" area
    And I go to "/logout"
    And I open the "/user/activate/" link from the email
    # terms
    When I press "agree_terms_save"
    Then the following fields should have an error:
      | agree_terms_agreeTermsUse |
    When I check "agree_terms_agreeTermsUse"
    And I press "agree_terms_save"
    Then the form should be valid
    # password step
    When I fill in the password fields with "Abcd1234"
    And I check "set_password_showTermsAndConditions"
    And I click on "save"
    Then the form should be valid
    # assert pre-fill
    Then the following fields should have the corresponding values:
      | user_details_firstname | Prof Main     |
      | user_details_lastname  | Contact |
    # fill form. Validation is skipepd as already tested in PA scenarios (same page)
    When I fill in the following:
      | user_details_firstname  | DD Admin   |
      | user_details_lastname   | Green      |
      | user_details_jobTitle   | Solicitor      |
      | user_details_phoneMain  | 1234554321 |
    And I press "user_details_save"
    Then the form should be valid
    And the URL should match "/org"
    # check I'm in the dashboard and can see the two clients of the org
#    And I should see the "client-34350001" region
#    And I should see the "client-34350002" region



