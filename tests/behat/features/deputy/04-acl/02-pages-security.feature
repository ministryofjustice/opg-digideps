Feature: deputy / acl / security on pages
    
    @deputy
    Scenario: create backup
       Given I save the application status into "pages-security-init"

    @deputy 
    Scenario: create another user with client and report with data
      # restore status of first report before submitting
      Given emails are sent from "admin" area
      And I reset the email log
      Given I load the application status from "report-submit-pre"
      Given I am logged in to admin as "ADMIN@PUBLICGUARDIAN.GSI.GOV.UK" with password "Abcd1234"
      When I create a new "ODR-disabled" "Lay Deputy" user "Malicious" "User" with email "behat-malicious@publicguardian.gsi.gov.uk"
      And I activate the user with password "Abcd1234"
      And I set the user details to:
          | name | Malicious | User | | | |
          | address | 102 Petty France | MOJ | London | SW1H 9AJ | GB |
          | phone | 020 3334 3555  | 020 1234 5678  | | | |
      When I set the client details to:
            | name | Malicious | Client | | | | 
            | caseNumber | 12345ABC | | | | |
            | courtDate | 1 | 1 | 2016 | | |
            | allowedCourtOrderTypes_0 | 2 | | | | |
            | address |  1 South Parade | First Floor  | Nottingham  | NG1 2HT  | GB |
            | phone | 0123456789  | | | | |
      And I set the report start date to "1/1/2016"
      And I set the report end date to "1/1/2016"
      Then the URL should match "report/\d+/overview"
      
    
    @deputy 
    Scenario: Malicious User cannot access other's pages
      # behat-user can access report n.2
      Given I am logged in as "behat-user@publicguardian.gsi.gov.uk" with password "Abcd1234"
      And I save the application status into "deputy-acl-before"
      Then the following pages should return the following status:
        | /report/1/overview  | 200 | 
        # decisions
        | /report/1/decisions | 200 | 
        | /report/1/decisions/1/edit | 200 |
        | /report/1/decisions/1/delete | 200 |
        | /report/1/decisions/add | 200 | 
        # contacts
        | /report/1/contacts | 200 | 
        | /report/1/contacts/1/edit | 200 |
        | /report/1/contacts/1/delete | 200 |
        | /report/1/contacts/add | 200 | 
        # assets
        | /report/1/assets | 200 | 
        | /report/1/assets/1/edit | 200 |
        | /report/1/assets/1/delete | 200 |
        | /report/1/assets/add | 200 | 
        | /report/1/assets/add-complete/Antiques | 200 | 
        # accounts
        | /report/1/accounts | 200 | 
        | /report/1/accounts/banks/upsert/1 | 200 | 
        | /report/1/accounts/banks/1/delete | 200 | 
      # behat-malicious CANNOT access the same URLs
      Given I am logged in as "behat-malicious@publicguardian.gsi.gov.uk" with password "Abcd1234"
      # reload the status (as some URLs calls might have deleted data)
      And I load the application status from "deputy-acl-before"
      When I go to "/report/2/overview"
      Then the following pages should return the following status:
        | /report/2/overview  | 200 | 
        | /report/1/overview  | 500 | 
        # decisions
        | /report/2/decisions | 200 | 
        | /report/1/decisions | 500 | 
        | /report/1/decisions/1/edit | 500 |
        | /report/1/decisions/1/delete | 500 |
        | /report/1/decisions/add | 500 | 
        # contacts
        | /report/2/contacts | 200 | 
        | /report/1/contacts | 500 | 
        | /report/1/contacts/1/edit | 500 |
        | /report/1/contacts/1/delete | 500 |
        | /report/1/contacts/add | 500 | 
        # assets
        | /report/2/assets | 200 | 
        | /report/1/assets | 500 | 
        | /report/1/assets/1/edit | 500 | 
        | /report/1/assets/1/delete | 500 |
        | /report/1/assets/add | 500 | 
        | /report/1/assets/add-complete/Antiques | 500 | 
        # accounts
        | /report/1/accounts | 500 | 
        | /report/1/accounts/banks/upsert/1 | 500 | 
        | /report/1/accounts/banks/1/delete | 500 | 
        # submit
        | /report/1/add_further_information | 500 | 
        | /report/1/declaration | 500 | 
        | /report/1/submitted | 500 | 
      And I load the application status from "deputy-acl-before"

    @deputy
    Scenario: restore backup
       Given I load the application status from "pages-security-init"

    
