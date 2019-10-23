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
       And I click on "client-detail-01000010"
       And I should see "DEP1 SURNAME1"
       And I should see "ADD1"
       And I should see "ADD2"
       And I should see "ADD3"
       And I should see "ADD4"
       And I should see "ADD5"
       And I should see "behat-prof1@publicguardian.gov.uk"
       And I should see "behat-deputy-email2@publicguardian.gov.uk"
       And I should see "behat-deputy-email3@publicguardian.gov.uk"



