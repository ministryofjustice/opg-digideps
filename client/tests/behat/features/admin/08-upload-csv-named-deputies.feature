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
       Then I click on "admin-client-search"
       And I click on "client-detail-34350001"
       And I should see "NAMED_FN NAMED_SN"
       And I should see "NAMED_ADD1"
       And I should see "NAMED_ADD2"
       And I should see "NAMED_ADD3"
       And I should see "NAMED_ADD4"
       And I should see "NAMED_ADD5"
       And I should see "email2@dd-professionals.co.uk"
       And I should see "email3@dd-professionals.co.uk"



