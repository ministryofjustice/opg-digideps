Feature: PA user edits report sections

  Scenario: PA user edit decisions section
    Given I load the application status from "team-users-complete"
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    Then the response status code should be 200
    And the URL should match "report/\d+/overview"
    And I click on "edit-decisions, start"
        # step  mental capacity
    Then the step with the following values CAN be submitted:
      | mental_capacity_hasCapacityChanged_1 | stayedSame |
    And the step with the following values CAN be submitted:
      | mental_assessment_mentalAssessmentDate_month | 01 |
      | mental_assessment_mentalAssessmentDate_year | 2017 |
        # chose "no records"
    Given the step cannot be submitted without making a selection
    Then the step with the following values CAN be submitted:
      | decision_exist_hasDecisions_1 | no |
      | decision_exist_reasonForNoDecisions | rfnd |

  Scenario: Pa saves a contact
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-contacts, start"
        # chose "no records"
    Given the step cannot be submitted without making a selection
    Then the step with the following values CAN be submitted:
      | contact_exist_hasContacts_1 | no |
      | contact_exist_reasonForNoContacts | rfnc |

  Scenario: PA visits and care steps
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-visits_care, start"
    # step 1 empty
    And the step cannot be submitted without making a selection
    # step 1 missing details
    And the step with the following values CAN be submitted:
      | visits_care_doYouLiveWithClient_1      | no    |
      | visits_care_howOftenDoYouContactClient | daily |
    # step 2 empty
    And the step cannot be submitted without making a selection
    # step 2 correct
    And the step with the following values CAN be submitted:
      | visits_care_doesClientReceivePaidCare_0 | yes                 |
      | visits_care_howIsCareFunded_0           | client_pays_for_all |
    # step 3 empty
    And the step cannot be submitted without making a selection
    # step 3 correct
    And the step with the following values CAN be submitted:
      | visits_care_whoIsDoingTheCaring | the brother |
    # step 4 empty
    And the step cannot be submitted without making a selection
    # step 4 correct
    Then the step with the following values CAN be submitted:
      | visits_care_doesClientHaveACarePlan_0         | yes  |
      | visits_care_whenWasCarePlanLastReviewed_month | 12   |
      | visits_care_whenWasCarePlanLastReviewed_year  | 2015 |

  Scenario: PA report actions
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-actions, start"
      # step 1
    And the step cannot be submitted without making a selection
    Then the step with the following values CAN be submitted:
      | action_doYouExpectFinancialDecisions_0      | yes    |
      | action_doYouExpectFinancialDecisionsDetails | dyefdd |
    # step 2
    And the step cannot be submitted without making a selection
    Then the step with the following values CAN be submitted:
      | action_doYouHaveConcerns_0      | yes   |
      | action_doYouHaveConcernsDetails | dyhcd |

  Scenario: PA any other info
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-other_info, start"
     # step 1
    And the step cannot be submitted without making a selection
    Then the step with the following values CAN be submitted:
      | more_info_actionMoreInfo_0      | yes  |
      | more_info_actionMoreInfoDetails | amid |

  Scenario: PA deputy expenses (No fees exist)
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-pa_fee_expense, start"
    # chose "no option"
    Given the step cannot be submitted without making a selection
    And the step with the following values cannot be submitted:
      | fee_exist_hasFees_1 | no |
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | fee_exist_reasonForNoFees | Some reason for no fees|
    # "Fees outside practice direction" question
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_paidForAnything_1 | no |
    # check record in summary page
    And each text should be present in the corresponding region:
      | no                            | no-contacts        |
      | Some reason for no fees       | reason-no-fees     |
      | no                            | paid-for-anything  |

  Scenario: PA gifts
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-gifts, start"
    # chose "no records"
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_giftsExist_1 | no |

  Scenario: PA assets
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-assets, start"
      # chose "no records"
    And the step with the following values CAN be submitted:
      | yes_no_noAssetToAdd_1 | 1 |

  Scenario: PA debts
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-debts, start"
      # chose "no records"
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_hasDebts_1 | no |

  Scenario: PA add account
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-bank_accounts, start"
    # step 1
    And the step with the following values CAN be submitted:
      | account_accountType_0 | current |
    # add account n.1 (current)
    And the step with the following values CAN be submitted:
      | account_bank                      | HSBC - main account |
      | account_accountNumber             | 01ca                |
      | account_sortCode_sort_code_part_1 | 11                  |
      | account_sortCode_sort_code_part_2 | 22                  |
      | account_sortCode_sort_code_part_3 | 33                  |
      | account_isJointAccount_1          | no                  |
    And the step with the following values CAN be submitted:
      | account_openingBalance | 100.40 |
      | account_closingBalance | 100.40 |
    # add another: no
    And I choose "no" when asked for adding another record

  Scenario: PA money in 102
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-money_in, start"
    # add transaction n.1 and check validation
    And the step with the following values CAN be submitted:
      | account_group_0 | pensions |
    And the step with the following values CAN be submitted:
      | account_category_0 | state-pension |
    And the step with the following values CAN be submitted:
      | account_description | pension received |
      | account_amount      | 50.00         |
    # add another: no
    And I choose "no" when asked for adding another record

  Scenario: PA money out
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-money_out, start"
      # add transaction n.1 and check validation
    And the step with the following values CAN be submitted:
      | account_group_0 | household-bills |
    And the step with the following values CAN be submitted:
      | account_category_0 | broadband |
    And the step with the following values CAN be submitted:
      | account_description | january bill |
      | account_amount      | 50.00     |
      # add another: no
    And I choose "no" when asked for adding another record

  Scenario: PA Report should be submittable
    Given I save the application status into "pa-report-balance-before"
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    Then the report should be submittable
    And I save the application status into "pa-report-completed"

  Scenario: PA money in 103
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000011" region
    And I click on "edit-money_in_short, start"
    And the step with the following values CAN be submitted:
      | money_short_moneyShortCategoriesIn_0_present | 1 |
      | money_short_moneyShortCategoriesIn_5_present | 1 |
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_moneyTransactionsShortInExist_1 | no |
    And each text should be present in the corresponding region:
      | State pension and benefits       | categories    |
      | Compensations and damages awards | categories    |
      | No                               | records-exist |
    Given I click on "edit" in the "records-exist" region
    And the step with the following values CAN be submitted:
      | yes_no_moneyTransactionsShortInExist_0 | yes |
    And the step with the following values CAN be submitted:
      | money_short_transaction_description | december salary |
      | money_short_transaction_amount      | 1400            |
    And I choose "no" when asked for adding another record
          # check record in summary page
    And each text should be present in the corresponding region:
      | december salary | transaction-december-salary |
      | £1,400.00       | transaction-december-salary |
      | £1,400.00       | transaction-total           |

  Scenario: PA money out 103
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000011" region
    And I click on "edit-money_out_short, start"
    And the step with the following values CAN be submitted:
      | money_short_moneyShortCategoriesOut_0_present | 1 |
      | money_short_moneyShortCategoriesOut_4_present | 1 |
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_moneyTransactionsShortOutExist_1 | no |
    And each text should be present in the corresponding region:
      | Accommodation costs | categories    |
      | personal allowance  | categories    |
      | No                  | records-exist |
    Given I click on "edit" in the "records-exist" region
    And the step with the following values CAN be submitted:
      | yes_no_moneyTransactionsShortOutExist_0 | yes |
    And the step with the following values CAN be submitted:
      | money_short_transaction_description | december rent |
      | money_short_transaction_amount      | 1401          |
    And I choose "no" when asked for adding another record
    And each text should be present in the corresponding region:
      | december rent | transaction-december-rent |
      | £1,401.00     | transaction-december-rent |
      | £1,401.00     | transaction-total         |
    And I save the application status into "pa-report-103-inprogress"
