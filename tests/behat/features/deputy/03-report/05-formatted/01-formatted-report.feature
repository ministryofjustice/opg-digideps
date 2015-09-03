Feature: deputy / report / Formatted Report

    @formatted-report @accounts @deputy
    Scenario: Setup the test user
      Given I am logged in to admin as "ADMIN@PUBLICGUARDIAN.GSI.GOV.UK" with password "Abcd1234"
      #Then I should see "admin@publicguardian.gsi.gov.uk" in the "users" region
      When I create a new "Lay Deputy" user "Wilma" "Smith" with email "behat-report@publicguardian.gsi.gov.uk"
      And I activate the user with password "Abcd1234"
      And I set the user details to:
          | name | John | Doe |
          | address | 102 Petty France | MOJ | London | SW1H 9AJ | GB |
          | phone | 020 3334 3555 | 020 1234 5678  |
      And I set the client details to:
            | name | Peter | White |
            | caseNumber | 123456ABC |
            | courtDate | 1 | 1 | 2014 |
            | allowedCourtOrderTypes_0 | 2 |
            | address |  1 South Parade | First Floor  | Nottingham  | NG1 2HT  | GB |
            | phone | 0123456789  |
      And I set the report end date to "1/1/2015"
      Then the URL should match "report/\d+/overview"
      Then I am on "/logout"
      And I reset the email log
      Then I save the application status into "reportuser"


    @formatted-report @deputy
    Scenario: Enter a report
        When I load the application status from "reportuser"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I add the following decisions:
          | description  | clientInvolved | clientInvolvedDetails |
          | 3 beds      | yes           | the client was able to decide at 85% |
          | 2 televisions | yes           | the client said he doesnt want a tv anymore |
        And I add the following contacts:
          | contactName | relationship | explanation                    | address       | address2  | county    | postcode | country |
          | Andy White  | brother      |  no explanation                | 45 Noth Road | Islington  | London    | N2 5JF   | GB      |
          | Fred Smith |  Social Worke  | Advices on benefits available | Town Hall     |Maidenhead | Berkshire | SL1 1RR  | GB |
        And I add the following assets:
            | title        | value       |  description       | valuationDate |
            | Vehicles    | 12000.00    |  Mini cooper       | 10/11/2015 |
            | Property    | 250000.0    | 2 beds flat in HA2 |            |
            | Vehicles    | 13000.00    | Alfa Romeo 156 JTD | 10/11/2015 |
        Then I save the application status into "reportwithoutmoney"
        And I add the following bank account:
            | bank    | HSBC - main account |
            | accountNumber | 8 | 7 | 6 | 5 |
            | sortCode | 88 | 77 | 66 |
            | openingDate   | 1/1/2014 |
            | openingBalance  | 155.000 |
            | moneyIn_0    | 10000.01 |
            | moneyIn_1    | 200.01 |
            | moneyIn_2    | 300.01 |
            | moneyIn_3    | 400.01 |
            | moneyIn_4    | 500.01 |
            | moneyIn_5    | 600.01 |
            | moneyIn_6    | 700.01 |
            | moneyIn_7    | 800.01 |
            | moneyIn_8    | 900.01 |
            | moneyIn_9    | 1000.01 |
            | moneyIn_10   | 1100.01 |
            | moneyIn_11   | 1,200.01 |
            | moneyIn_12   | 1,300.01 |
            | moneyIn_13   | 1,400.01 |
            | moneyIn_14   | 1,500.01 |
            | moneyIn_15   | 1,600.01 | more-details-in-15 |
            | moneyIn_16   | 1,700.01 | more-details-in-16 |
            | moneyIn_17   | 1,800.01 | more-details-in-17 |
            | moneyIn_18   | 1,800.01 | more-details-in-18 |
            | moneyOut_0   | 100.00 |
            | moneyOut_1   | 200.00 |
            | moneyOut_2   | 300.00 |
            | moneyOut_3   | 400.00 |
            | moneyOut_4   | 500.00 |
            | moneyOut_5   | 600.00 |
            | moneyOut_6   | 700.00 |
            | moneyOut_7   | 800.00 |
            | moneyOut_8   | 900.00 |
            | moneyOut_9   | 1000.00 |
            | moneyOut_10  | 1100.00 |
            | moneyOut_11  | 1,200.00 | more-details-out-11 |
            | moneyOut_12  | 1,300.00 | more-details-out-12 |
            | moneyOut_13  | 1,400.00 | more-details-out-13 |
            | moneyOut_14  | 1,500.00 | more-details-out-14 |
            | moneyOut_15  | 1,600.00 | more-details-out-15 |
            | moneyOut_16  | 1,700.00 | more-details-out-16 |
            | moneyOut_17  | 1,800.00 | more-details-out-17 |
            | moneyOut_18  | 1,900.00 | more-details-out-18 |
            | moneyOut_19  | 2,000.00 | more-details-out-19 |
            | moneyOut_20  | 2,100.00 | more-details-out-20 |
            | closingDate    | 1 /1/2015 |
            | closingBalance | 5855.19 |
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        Then I submit the report with further info "More info."
        And I save the application status into "reportsubmitted"

    @formatted-report @deputy
    Scenario: A report lists decisions
        When I load the application status from "reportsubmitted"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the formatted report
        Then the response status code should be 200
        And I should see "Deputy report for property and financial decisions"
        And I should see "3 beds" in "decisions-section"
        And I should see "the client was able to decide at 85%" in "decisions-section"
        And I should see "2 televisions" in "decisions-section"
        And I should see "the client said he doesnt want a tv anymore" in "decisions-section"

    @formatted-report @deputy
    Scenario: A report says why no decisions were made
        When I load the application status from "reportuser"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        Then I say there were no decisions made because "small budget"
        When I add the following contacts:
          | contactName | relationship | explanation                    | address       | address2  | county    | postcode | country |
          | Andy White  | brother      |  no explanation                | 45 Noth Road | Islington  | London    | N2 5JF   | GB      |
          | Fred Smith |  Social Worke  | Advices on benefits available | Town Hall     |Maidenhead | Berkshire | SL1 1RR  | GB |
        And I add the following bank account:
            | bank    | HSBC - main account |
            | accountNumber | 8 | 7 | 6 | 5 |
            | sortCode | 88 | 77 | 66 |
            | openingDate   | 1/1/2014 |
            | openingBalance  | 155.000 |
            | moneyIn_0    | 10000.01 |
            | moneyIn_1    | 200.01 |
            | moneyIn_2    | 300.01 |
            | moneyIn_3    | 400.01 |
            | moneyIn_4    | 500.01 |
            | moneyIn_5    | 600.01 |
            | moneyIn_6    | 700.01 |
            | moneyIn_7    | 800.01 |
            | moneyIn_8    | 900.01 |
            | moneyIn_9    | 1000.01 |
            | moneyIn_10   | 1100.01 |
            | moneyIn_11   | 1,200.01 |
            | moneyIn_12   | 1,300.01 |
            | moneyIn_13   | 1,400.01 |
            | moneyIn_14   | 1,500.01 |
            | moneyIn_15   | 1,600.01 | more-details-in-15 |
            | moneyIn_16   | 1,700.01 | more-details-in-16 |
            | moneyIn_17   | 1,800.01 | more-details-in-17 |
            | moneyIn_18   | 1,800.01 | more-details-in-18 |
            | moneyOut_0   | 100.00 |
            | moneyOut_1   | 200.00 |
            | moneyOut_2   | 300.00 |
            | moneyOut_3   | 400.00 |
            | moneyOut_4   | 500.00 |
            | moneyOut_5   | 600.00 |
            | moneyOut_6   | 700.00 |
            | moneyOut_7   | 800.00 |
            | moneyOut_8   | 900.00 |
            | moneyOut_9   | 1000.00 |
            | moneyOut_10  | 1100.00 |
            | moneyOut_11  | 1,200.00 | more-details-out-11 |
            | moneyOut_12  | 1,300.00 | more-details-out-12 |
            | moneyOut_13  | 1,400.00 | more-details-out-13 |
            | moneyOut_14  | 1,500.00 | more-details-out-14 |
            | moneyOut_15  | 1,600.00 | more-details-out-15 |
            | moneyOut_16  | 1,700.00 | more-details-out-16 |
            | moneyOut_17  | 1,800.00 | more-details-out-17 |
            | moneyOut_18  | 1,900.00 | more-details-out-18 |
            | moneyOut_19  | 2,000.00 | more-details-out-19 |
            | moneyOut_20  | 2,100.00 | more-details-out-20 |
            | closingDate    | 1 /1/2015 |
            | closingBalance | 5855.19 |
        # Finally, Assets
        When I add the following assets:
          | title        | value       |  description        | valuationDate |
          | Property    | 250000.00    |  2 beds flat in HA2 |               |
          | Vehicles    | 13000.00     |  Alfa Romeo 156 JTD |    10/11/2015  |
        And I set the following safeguarding information:
          | safeguarding_doYouLiveWithClient_0 | yes |
          | safeguarding_doesClientReceivePaidCare_1 | no |
          | safeguarding_doesClientHaveACarePlan_1 | no |
          | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I submit the report with further info "More info."
        And I view the formatted report
        Then the response status code should be 200
        And I should see "Deputy report for property and financial decisions"
        Then I should see "No decisions made:" in "decisions-section"
        And I should see "small budget" in "decisions-section"

    #Scenario: A report shows contacts
    @formatted-report @deputy
    Scenario: A report lists contacts
        When I load the application status from "reportsubmitted"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the formatted report
        And I should see "Deputy report for property and financial decisions"
        And I should see "Andy White" in "contacts-section"
        And I should see "Fred Smith" in "contacts-section"

    @formatted-report @deputy
    Scenario: A report describes why there are no contacts
        When I load the application status from "reportuser"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I add the following decisions:
           | description   | clientInvolved | clientInvolvedDetails |
           | 3 beds      | yes            | the client was able to decide at 85% |
           | 2 televisions | yes            | the client said he doesnt want a tv anymore |
        # Next, some contacts
        Then I say there were no contacts because "kept in the book"
        And I add the following bank account:
            | bank    | HSBC - main account |
            | accountNumber | 8 | 7 | 6 | 5 |
            | sortCode | 88 | 77 | 66 |
            | openingDate   | 1/1/2014 |
            | openingBalance  | 155.000 |
            | moneyIn_0    | 10000.01 |
            | moneyIn_1    | 200.01 |
            | moneyIn_2    | 300.01 |
            | moneyIn_3    | 400.01 |
            | moneyIn_4    | 500.01 |
            | moneyIn_5    | 600.01 |
            | moneyIn_6    | 700.01 |
            | moneyIn_7    | 800.01 |
            | moneyIn_8    | 900.01 |
            | moneyIn_9    | 1000.01 |
            | moneyIn_10   | 1100.01 |
            | moneyIn_11   | 1,200.01 |
            | moneyIn_12   | 1,300.01 |
            | moneyIn_13   | 1,400.01 |
            | moneyIn_14   | 1,500.01 |
            | moneyIn_15   | 1,600.01 | more-details-in-15 |
            | moneyIn_16   | 1,700.01 | more-details-in-16 |
            | moneyIn_17   | 1,800.01 | more-details-in-17 |
            | moneyIn_18   | 1,800.01 | more-details-in-18 |
            | moneyOut_0   | 100.00 |
            | moneyOut_1   | 200.00 |
            | moneyOut_2   | 300.00 |
            | moneyOut_3   | 400.00 |
            | moneyOut_4   | 500.00 |
            | moneyOut_5   | 600.00 |
            | moneyOut_6   | 700.00 |
            | moneyOut_7   | 800.00 |
            | moneyOut_8   | 900.00 |
            | moneyOut_9   | 1000.00 |
            | moneyOut_10  | 1100.00 |
            | moneyOut_11  | 1,200.00 | more-details-out-11 |
            | moneyOut_12  | 1,300.00 | more-details-out-12 |
            | moneyOut_13  | 1,400.00 | more-details-out-13 |
            | moneyOut_14  | 1,500.00 | more-details-out-14 |
            | moneyOut_15  | 1,600.00 | more-details-out-15 |
            | moneyOut_16  | 1,700.00 | more-details-out-16 |
            | moneyOut_17  | 1,800.00 | more-details-out-17 |
            | moneyOut_18  | 1,900.00 | more-details-out-18 |
            | moneyOut_19  | 2,000.00 | more-details-out-19 |
            | moneyOut_20  | 2,100.00 | more-details-out-20 |
            | closingDate    | 1 /1/2015 |
            | closingBalance | 5855.19 |
        When I add the following assets:
          | title        | value       |  description        | valuationDate |
          | Property    | 250000.00    |  2 beds flat in HA2 |               |
          | Vehicles    | 13000.00     |  Alfa Romeo 156 JTD |    10/11/2015  |
        And I set the following safeguarding information:
          | safeguarding_doYouLiveWithClient_0 | yes |
          | safeguarding_doesClientReceivePaidCare_1 | no |
          | safeguarding_doesClientHaveACarePlan_1 | no |
          | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I submit the report with further info "More info."
        # Now view the report
        And I view the formatted report
        Then the response status code should be 200
        And I should see "Deputy report for property and financial decisions"
        And I should see "kept in the book" in "contacts-section"

    @formatted-report @deputy
    Scenario: A report shows the account name and numbers
        When I load the application status from "reportsubmitted"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the formatted report
        And I should see "HSBC - main account" in "accounts-section"
        And I should see "Current account" in "accounts-section"
        And I should see "88" in "accounts-section"
        And I should see "77" in "accounts-section"
        And I should see "66" in "accounts-section"
        And I should see "8765" in "accounts-section"

    @formatted-report @deputy
    Scenario: A report lists money paid out for an account
        When I load the application status from "reportsubmitted"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the formatted report
        Then I should see "Summary of money paid out"
        And I should see "Care fees or local authority charges for care" in "money-out"
        And I should see "£ 100.00" in "money-out"
        And I should see "more-details-out-11" in "money-out"

    @formatted-report @deputy
    Scenario: A report lists money paid in to an account
        When I load the application status from "reportsubmitted"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the formatted report
        Then I should see "Summary of money paid out"
        And I should see "Care fees or local authority charges for care" in "money-out"
        And I should see "£ 10,000.01" in "money-in"
        And I should see "more-details-in-15" in "money-in"

    @formatted-report @deputy
    Scenario: A report lists total money in, out, the different and the actual
        When I load the application status from "reportwithoutmoney"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I add the following bank account:
            | bank    | HSBC - main account |
            | accountNumber | 8 | 7 | 6 | 5 |
            | sortCode | 88 | 77 | 66 |
            | openingDate   | 1/1/2014 |
            | openingBalance  | 155.000 |
            | moneyIn_0    | 100.01 |
            | moneyIn_1    | 200.01 |
            | moneyIn_2    | 300.01 |
            | moneyIn_3    | 400.01 |
            | moneyIn_4    | 500.01 |
            | moneyIn_5    | 600.01 |
            | moneyIn_6    | 700.01 |
            | moneyIn_7    | 800.01 |
            | moneyIn_8    | 900.01 |
            | moneyIn_9    | 1000.01 |
            | moneyIn_10   | 1100.01 |
            | moneyIn_11   | 1,200.01 |
            | moneyIn_12   | 1,300.01 |
            | moneyIn_13   | 1,400.01 |
            | moneyIn_14   | 1,500.01 |
            | moneyIn_15   | 1,600.01 | more-details-in-15 |
            | moneyIn_16   | 1,700.01 | more-details-in-16 |
            | moneyIn_17   | 1,800.01 | more-details-in-17 |
            | moneyIn_18   | 10,800.01 | more-details-in-18 |
            | moneyOut_0   | 100.00 |
            | moneyOut_1   | 200.00 |
            | moneyOut_2   | 300.00 |
            | moneyOut_3   | 400.00 |
            | moneyOut_4   | 500.00 |
            | moneyOut_5   | 600.00 |
            | moneyOut_6   | 700.00 |
            | moneyOut_7   | 800.00 |
            | moneyOut_8   | 900.00 |
            | moneyOut_9   | 1000.00 |
            | moneyOut_10  | 1100.00 |
            | moneyOut_11  | 1,200.00 | more-details-out-11 |
            | moneyOut_12  | 1,300.00 | more-details-out-12 |
            | moneyOut_13  | 1,400.00 | more-details-out-13 |
            | moneyOut_14  | 1,500.00 | more-details-out-14 |
            | moneyOut_15  | 1,600.00 | more-details-out-15 |
            | moneyOut_16  | 1,700.00 | more-details-out-16 |
            | moneyOut_17  | 1,800.00 | more-details-out-17 |
            | moneyOut_18  | 1,900.00 | more-details-out-18 |
            | moneyOut_19  | 2,000.00 | more-details-out-19 |
            | moneyOut_20  | 2,200.00 | more-details-out-20 |
            | closingDate    | 1 /1/2015 |
            | closingBalance | 4855.19 |
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I submit the report with further info "More info."
        # Now view the report
        And I view the formatted report
        Then I should see "Balancing the account"
        And I should see "155.00" in "balancing-opening-balance"
        And I should see "27,900.19" in "balancing-total-in"
        And I should see "28,055.19" in "balancing-sub-total"
        And I should see "23,200.00" in "balancing-total-out"
        And I should see "4,855.19" in "balancing-sub-total-2"
        And I should see "4,855.19" in "balancing-closing-balance"

    @formatted-report @deputy
    Scenario: A report explains why the balance doesnt match the statement
        When I load the application status from "reportwithoutmoney"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I add the following bank account:
            | bank    | HSBC - main account |
            | accountNumber | 8 | 7 | 6 | 5 |
            | sortCode | 88 | 77 | 66 |
            | openingDate   | 1/1/2014 |
            | openingBalance  | 155.000 |
            #
            | moneyIn_0    | 100.01 |
            | moneyIn_1    | 200.01 |
            | moneyIn_2    | 300.01 |
            | moneyIn_3    | 400.01 |
            | moneyIn_4    | 500.01 |
            | moneyIn_5    | 600.01 |
            | moneyIn_6    | 700.01 |
            | moneyIn_7    | 800.01 |
            | moneyIn_8    | 900.01 |
            | moneyIn_9    | 1000.01 |
            | moneyIn_10   | 1100.01 |
            | moneyIn_11   | 1,200.01 |
            | moneyIn_12   | 1,300.01 |
            | moneyIn_13   | 1,400.01 |
            | moneyIn_14   | 1,500.01 |
            | moneyIn_15   | 1,600.01 | more-details-in-15 |
            | moneyIn_16   | 1,700.01 | more-details-in-16 |
            | moneyIn_17   | 1,800.01 | more-details-in-17 |
            | moneyIn_18   | 1,800.01 | more-details-in-18 |
            #
            | moneyOut_0   | 100.00 |
            | moneyOut_1   | 200.00 |
            | moneyOut_2   | 300.00 |
            | moneyOut_3   | 400.00 |
            | moneyOut_4   | 500.00 |
            | moneyOut_5   | 600.00 |
            | moneyOut_6   | 700.00 |
            | moneyOut_7   | 800.00 |
            | moneyOut_8   | 900.00 |
            | moneyOut_9   | 1000.00 |
            | moneyOut_10  | 1100.00 |
            | moneyOut_11  | 1,200.00 | more-details-out-11 |
            | moneyOut_12  | 1,300.00 | more-details-out-12 |
            | moneyOut_13  | 1,400.00 | more-details-out-13 |
            | moneyOut_14  | 1,500.00 | more-details-out-14 |
            | moneyOut_15  | 1,600.00 | more-details-out-15 |
            | moneyOut_16  | 1,700.00 | more-details-out-16 |
            | moneyOut_17  | 1,800.00 | more-details-out-17 |
            | moneyOut_18  | 1,900.00 | more-details-out-18 |
            | moneyOut_19  | 2,000.00 | more-details-out-19 |
            | moneyOut_20  | 2,100.00 | more-details-out-20 |
            #
            | closingDate    | 1 /1/2015 |
            | closingBalance | 155.00 |
            #∑
            | closingBalanceExplanation | £ 100.50 moved to other account |
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I submit the report with further info "More info."
        And I view the formatted report
        And I should see "£ 100.50 moved to other account" in "accountBalance_closingBalanceExplanation"

    @formatted-report @deputy
    Scenario: A report explains why the opening date is off
        When I load the application status from "reportwithoutmoney"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I add the following bank account:
            | bank    | HSBC - main account |
            | accountNumber | 8 | 7 | 6 | 5 |
            | sortCode | 88 | 77 | 66 |
            | openingDate   | 1/1/2014 |
            | openingDateExplanation    | earlier transaction made with other account |
            | openingBalance  | 155.000 |
            #
            | moneyIn_0    | 100.01 |
            | moneyIn_1    | 200.01 |
            | moneyIn_2    | 300.01 |
            | moneyIn_3    | 400.01 |
            | moneyIn_4    | 500.01 |
            | moneyIn_5    | 600.01 |
            | moneyIn_6    | 700.01 |
            | moneyIn_7    | 800.01 |
            | moneyIn_8    | 900.01 |
            | moneyIn_9    | 1000.01 |
            | moneyIn_10   | 1100.01 |
            | moneyIn_11   | 1,200.01 |
            | moneyIn_12   | 1,300.01 |
            | moneyIn_13   | 1,400.01 |
            | moneyIn_14   | 1,500.01 |
            | moneyIn_15   | 1,600.01 | more-details-in-15 |
            | moneyIn_16   | 1,700.01 | more-details-in-16 |
            | moneyIn_17   | 1,800.01 | more-details-in-17 |
            | moneyIn_18   | 1,800.01 | more-details-in-18 |
            #
            | moneyOut_0   | 100.00 |
            | moneyOut_1   | 200.00 |
            | moneyOut_2   | 300.00 |
            | moneyOut_3   | 400.00 |
            | moneyOut_4   | 500.00 |
            | moneyOut_5   | 600.00 |
            | moneyOut_6   | 700.00 |
            | moneyOut_7   | 800.00 |
            | moneyOut_8   | 900.00 |
            | moneyOut_9   | 1000.00 |
            | moneyOut_10  | 1100.00 |
            | moneyOut_11  | 1,200.00 | more-details-out-11 |
            | moneyOut_12  | 1,300.00 | more-details-out-12 |
            | moneyOut_13  | 1,400.00 | more-details-out-13 |
            | moneyOut_14  | 1,500.00 | more-details-out-14 |
            | moneyOut_15  | 1,600.00 | more-details-out-15 |
            | moneyOut_16  | 1,700.00 | more-details-out-16 |
            | moneyOut_17  | 1,800.00 | more-details-out-17 |
            | moneyOut_18  | 1,900.00 | more-details-out-18 |
            | moneyOut_19  | 2,000.00 | more-details-out-19 |
            | moneyOut_20  | 2,100.00 | more-details-out-20 |
            #
            | closingDate    | 1 /1/2015 |
            | closingBalance | 155.00 |
            #∑
            | closingBalanceExplanation | £ 100.50 moved to other account |
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I submit the report with further info "More info."
        # Now view the report
        And I view the formatted report
        And I should see "earlier transaction made with other account" in "account-date-explanation"

    @formatted-report @deputy
    Scenario: A report explains why the closing date is off
        When I load the application status from "reportwithoutmoney"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I add the following bank account:
            | bank    | HSBC - main account |
            | accountNumber | 8 | 7 | 6 | 5 |
            | sortCode | 88 | 77 | 66 |
            | openingDate   | 1/2/2014 |
            | openingDateExplanation  | earlier transaction made with other account |
            | openingBalance  | 155.000 |
            #
            | moneyIn_0    | 100.01 |
            | moneyIn_1    | 200.01 |
            | moneyIn_2    | 300.01 |
            | moneyIn_3    | 400.01 |
            | moneyIn_4    | 500.01 |
            | moneyIn_5    | 600.01 |
            | moneyIn_6    | 700.01 |
            | moneyIn_7    | 800.01 |
            | moneyIn_8    | 900.01 |
            | moneyIn_9    | 1000.01 |
            | moneyIn_10   | 1100.01 |
            | moneyIn_11   | 1,200.01 |
            | moneyIn_12   | 1,300.01 |
            | moneyIn_13   | 1,400.01 |
            | moneyIn_14   | 1,500.01 |
            | moneyIn_15   | 1,600.01 | more-details-in-15 |
            | moneyIn_16   | 1,700.01 | more-details-in-16 |
            | moneyIn_17   | 1,800.01 | more-details-in-17 |
            | moneyIn_18   | 1,800.01 | more-details-in-18 |
            #
            | moneyOut_0   | 100.00 |
            | moneyOut_1   | 200.00 |
            | moneyOut_2   | 300.00 |
            | moneyOut_3   | 400.00 |
            | moneyOut_4   | 500.00 |
            | moneyOut_5   | 600.00 |
            | moneyOut_6   | 700.00 |
            | moneyOut_7   | 800.00 |
            | moneyOut_8   | 900.00 |
            | moneyOut_9   | 1000.00 |
            | moneyOut_10  | 1100.00 |
            | moneyOut_11  | 1,200.00 | more-details-out-11 |
            | moneyOut_12  | 1,300.00 | more-details-out-12 |
            | moneyOut_13  | 1,400.00 | more-details-out-13 |
            | moneyOut_14  | 1,500.00 | more-details-out-14 |
            | moneyOut_15  | 1,600.00 | more-details-out-15 |
            | moneyOut_16  | 1,700.00 | more-details-out-16 |
            | moneyOut_17  | 1,800.00 | more-details-out-17 |
            | moneyOut_18  | 1,900.00 | more-details-out-18 |
            | moneyOut_19  | 2,000.00 | more-details-out-19 |
            | moneyOut_20  | 2,100.00 | more-details-out-20 |
            #
            | closingDate    | 11 /11/2014 |
            | closingDateExplanation    | closing date explanation |
            | closingBalance | 4855.19 |
            #∑
            | closingBalanceExplanation | £ 100.50 moved to other account |
        And I set the following safeguarding information:
            | safeguarding_doYouLiveWithClient_0 | yes |
            | safeguarding_doesClientReceivePaidCare_1 | no |
            | safeguarding_doesClientHaveACarePlan_1 | no |
            | safeguarding_whoIsDoingTheCaring | Fred Jones |
        And I submit the report with further info "More info."
        # Now view the report
        And I view the formatted report
        And I should see "closing date explanation" in "account-date-explanation"

    @formatted-report @deputy
    Scenario: A report lists asset types in order
        When I load the application status from "reportsubmitted"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the formatted report
        And I should see "Client’s assets and debts"
        Then the 1 asset group should be "Property"
        And the 2 asset group should be "Vehicles"

    @formatted-report @deputy
    Scenario: A report lists asset details
        When I load the application status from "reportsubmitted"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the formatted report
        And the 1 asset in the "Vehicles" asset group should have a "description" "Mini cooper"
        And the 1 asset in the "Vehicles" asset group should have a "valuationDate" "10 / 11 / 2015"
        And the 1 asset in the "Vehicles" asset group should have a "value" "£12,000.00"

    @formatted-report @deputy
    Scenario: A report shows blank valuation date if there isn't one
        When I load the application status from "reportsubmitted"
        And I am logged in as "behat-report@publicguardian.gsi.gov.uk" with password "Abcd1234"
        And I view the formatted report
        And the 1 asset in the "Property" asset group should have an empty "valuationDate"

