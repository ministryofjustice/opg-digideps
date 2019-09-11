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
     Then I should see "Added 0 Prof users, 0 PA users, 3 clients, 3 named deputies and 0 reports."
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
