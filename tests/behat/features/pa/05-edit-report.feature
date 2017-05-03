Feature: PA user edits report sections

  Scenario: PA user edit decisions section
    Given I load the application status from "pa-users-uploaded"
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-decisions, start"
        # step  mental capacity
    Then the step cannot be submitted without making a selection
    Then the step with the following values CANNOT be submitted:
      | mental_capacity_hasCapacityChanged_0 | changed |
    And the step with the following values CAN be submitted:
      | mental_capacity_hasCapacityChanged_1 | stayedSame |
    And I go back from the step
    And the step with the following values CAN be submitted:
      | mental_capacity_hasCapacityChanged_0 | changed |
      | mental_capacity_hasCapacityChangedDetails | mchccd |
        # mental assessment date step
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | mental_assessment_mentalAssessmentDate_month | 01 |
      | mental_assessment_mentalAssessmentDate_year | 2017 |
        # chose "no records"
    Given the step cannot be submitted without making a selection
    Then the step with the following values CANNOT be submitted:
      | decision_exist_hasDecisions_1 | no |
    And the step with the following values CAN be submitted:
      | decision_exist_hasDecisions_1 | no |
      | decision_exist_reasonForNoDecisions | rfnd |
        # summary page check
    And each text should be present in the corresponding region:
      | Changed | mental-capacity     |
      | mchccd  | mental-capacity-changed-details     |
      | No      | has-decisions       |
      | rfnd    | reason-no-decisions |
        # select there are records (from summary page link)
    Given I click on "edit" in the "has-decisions" region
    And the step with the following values CAN be submitted:
      | decision_exist_hasDecisions_1 | yes |
        # add decision n.1 (and validate form)
    And the step cannot be submitted without making a selection
    And the step with the following values CANNOT be submitted:
      | decision_description |  |
      | decision_clientInvolvedBoolean_0 | 1 |
      | decision_clientInvolvedDetails |  |
    And the step with the following values CAN be submitted:
      | decision_description | dd1 |
      | decision_clientInvolvedBoolean_0 | 1 |
      | decision_clientInvolvedDetails | dcid1 |
        # add decision n.2
    And I choose "yes" when asked for adding another record
    And the step with the following values CAN be submitted:
      | decision_description | dd2 |
      | decision_clientInvolvedBoolean_0 | 1 |
      | decision_clientInvolvedDetails | dcid2 |
        # add another: no
    And I choose "no" when asked for adding another record
        # check record in summary page
    And each text should be present in the corresponding region:
      | dd1   | decision-1 |
      | Yes | decision-1 |
      | dcid1 | decision-1 |
      | dd2   | decision-2 |
      | Yes | decision-2 |
      | dcid2 | decision-2 |
        # remove decision n.2
    When I click on "delete" in the "decision-2" region
    Then I should not see the "decision-2" region
        # test add link
    When I click on "add"
    Then I should see the "save-and-continue" link
    When I go back from the step
        # edit decision n.1
    When I click on "edit" in the "decision-1" region
    Then the following fields should have the corresponding values:
      | decision_description | dd1 |
      | decision_clientInvolvedBoolean_0 | 1 |
      | decision_clientInvolvedDetails | dcid1 |
    And the step with the following values CAN be submitted:
      | decision_description | dd1-changed |
      | decision_clientInvolvedBoolean_1 | 0 |
      | decision_clientInvolvedDetails | dcid1-changed |
    And each text should be present in the corresponding region:
      | dd1-changed   | decision-1 |
      | No | decision-1 |
      | dcid1-changed | decision-1 |

  Scenario: contacts
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-contacts, start"
        # chose "no records"
    Given the step cannot be submitted without making a selection
    Then the step with the following values CANNOT be submitted:
      | contact_exist_hasContacts_1 | no |
    And the step with the following values CAN be submitted:
      | contact_exist_hasContacts_1 | no |
      | contact_exist_reasonForNoContacts | rfnc |
        # summary page check
    And each text should be present in the corresponding region:
      | No      | has-contacts       |
      | rfnc    | reason-no-contacts |
        # select there are records (from summary page link)
    Given I click on "edit" in the "has-contacts" region
    And the step with the following values CAN be submitted:
      | contact_exist_hasContacts_0 | yes |
        # add contact n.1 (and validate form)
    And the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | contact_contactName | Andy White  |
      | contact_relationship | GP |
      | contact_explanation | I owe him money |
      |  contact_address| 45 Noth Road |
      | contact_address2 | Islington |
      | contact_county | London |
      | contact_postcode | N2 AW1 |
      | contact_country | GB |
        # add contact n.2
    And I choose "yes" when asked for adding another record
    And the step with the following values CAN be submitted:
      | contact_contactName | Peter Black  |
      | contact_relationship | friend |
      | contact_explanation | I owe him lots of money |
      |  contact_address| 45 Noth Road2 |
      | contact_address2 | Islington2 |
      | contact_county | London2 |
      | contact_postcode | SW1 PB1 |
      | contact_country | GB |
        # add another: no
    And I choose "no" when asked for adding another record
        # check record in summary page
    And each text should be present in the corresponding region:
      | Andy White   | contact-n2-aw1 |
      | GP | contact-n2-aw1 |
      | I owe him money | contact-n2-aw1 |
      | Peter Black   | contact-sw1-pb1 |
      | friend | contact-sw1-pb1 |
      | I owe him lots of money | contact-sw1-pb1 |
        # remove contact n.2
    When I click on "delete" in the "contact-sw1-pb1" region
    Then I should not see the "contact-sw1-pb1" region
        # test add link
    When I click on "add"
    Then I should see the "save-and-continue" link
    When I go back from the step
        # edit contact n.1
    When I click on "edit" in the "contact-n2-aw1" region
    Then the following fields should have the corresponding values:
      | contact_contactName | Andy White  |
      | contact_relationship | GP |
      | contact_explanation | I owe him money |
      |  contact_address| 45 Noth Road |
      | contact_address2 | Islington |
      | contact_county | London |
      | contact_postcode | N2 AW1 |
      | contact_country | GB |
    And the step with the following values CAN be submitted:
      | contact_contactName | Andy Whites  |
      | contact_relationship | my GP |
      | contact_explanation | he takes care of my health |
      |  contact_address| 46 Noth Road |
      | contact_address2 | Camden |
      | contact_county | Greater London  |
      | contact_postcode | N2 AW2 |
      | contact_country | FR |
    And each text should be present in the corresponding region:
      | Andy Whites   | contact-n2-aw2 |
      | 46 Noth Road | contact-n2-aw2 |
      | Greater London | contact-n2-aw2 |
      | France | contact-n2-aw2 |
      | my GP | contact-n2-aw2 |
      | he takes care of my health | contact-n2-aw2 |

  Scenario: visits and care steps
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-visits_care, start"
    # step 1 empty
    And the step cannot be submitted without making a selection
    # step 1 missing details
    And the step with the following values CANNOT be submitted:
      | visits_care_doYouLiveWithClient_1      | no |       |
      | visits_care_howOftenDoYouContactClient |    | [ERR] |
    # step 1 correct
    And the step with the following values CAN be submitted:
      | visits_care_doYouLiveWithClient_1      | no    |
      | visits_care_howOftenDoYouContactClient | daily |
    # go back, check content, skip
    When I go back from the step
    Then the following fields should have the corresponding values:
      | visits_care_doYouLiveWithClient_1      | no    |
      | visits_care_howOftenDoYouContactClient | daily |
    Then I click on "step-skip"
    # step 2 empty
    And the step cannot be submitted without making a selection
    # step 2 missing details
    And the step with the following values CANNOT be submitted:
      | visits_care_doesClientReceivePaidCare_0 | yes |       |
    # step 2 correct
    And the step with the following values CAN be submitted:
      | visits_care_doesClientReceivePaidCare_0 | yes                 |
      | visits_care_howIsCareFunded_0           | client_pays_for_all |
    # go back, check content, skip
    When I go back from the step
    Then the following fields should have the corresponding values:
      | visits_care_doesClientReceivePaidCare_0 | yes                 |
      | visits_care_howIsCareFunded_0           | client_pays_for_all |
    Then I click on "step-skip"
    # step 3 empty
    And the step cannot be submitted without making a selection
    # step 3 correct
    And the step with the following values CAN be submitted:
      | visits_care_whoIsDoingTheCaring | the brother |
    # go back, check content, skip
    When I go back from the step
    Then the following fields should have the corresponding values:
      | visits_care_whoIsDoingTheCaring | the brother |
    Then I click on "step-skip"
    # step 4 empty
    And the step cannot be submitted without making a selection
    # step 4 missing details
    Then the step with the following values CANNOT be submitted:
      | visits_care_doesClientHaveACarePlan_0         | yes   | [ERR] |
      | visits_care_whenWasCarePlanLastReviewed_month |       | [ERR] |
      | visits_care_whenWasCarePlanLastReviewed_year  |       | [ERR] |
    # step 4 correct
    And the step with the following values CAN be submitted:
      | visits_care_doesClientHaveACarePlan_0         | yes  |
      | visits_care_whenWasCarePlanLastReviewed_month | 12   |
      | visits_care_whenWasCarePlanLastReviewed_year  | 2015 |
    # Summary overview
    Then each text should be present in the corresponding region:
      | No                    | live-with-client              |
      | daily                 | how-often-contact-client      |
      | Yes                   | does-client-receive-paid-care |
      | pays for all the care | how-is-care-funded            |
      | the brother           | who-is-doing-caring           |
      | Yes                   | client-has-care-plan          |
      | December 2015         | care-plan-last-reviewed       |
    # edit and check back link
    When I click on "live-with-client-edit, step-back"
    # edit
    When I click on "live-with-client-edit"
    And the step with the following values CAN be submitted:
      | visits_care_doYouLiveWithClient_0 | yes |
    # check edited
    Then I should see "Yes" in the "live-with-client" region
    And I should not see the "how-often-contact-client" region

  Scenario: report actions
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-actions, start"
      # step 1
    And the step cannot be submitted without making a selection
    Then the step with the following values CANNOT be submitted:
      | action_doYouExpectFinancialDecisions_0      | yes |       |
      | action_doYouExpectFinancialDecisionsDetails |     | [ERR] |
    Then the step with the following values CAN be submitted:
      | action_doYouExpectFinancialDecisions_0      | yes    |
      | action_doYouExpectFinancialDecisionsDetails | dyefdd |
    # step 2
    And the step cannot be submitted without making a selection
    Then the step with the following values CANNOT be submitted:
      | action_doYouHaveConcerns_0      | yes |       |
      | action_doYouHaveConcernsDetails |     | [ERR] |
    Then the step with the following values CAN be submitted:
      | action_doYouHaveConcerns_0      | yes   |
      | action_doYouHaveConcernsDetails | dyhcd |
    # check summary page
    And each text should be present in the corresponding region:
      | Yes    | expect-financial-decision         |
      | dyefdd | expect-financial-decision-details |
      | Yes    | have-concerns                     |
      | dyhcd  | have-concerns-details             |
    # check step 1 reloaded
    When I click on "edit" in the "expect-financial-decision" region
    Then the following fields should have the corresponding values:
      | action_doYouExpectFinancialDecisions_0      | yes    |
      | action_doYouExpectFinancialDecisionsDetails | dyefdd |
    And I go back from the step
    # check step 2 reloaded
    When I click on "edit" in the "have-concerns" region
    Then the following fields should have the corresponding values:
      | action_doYouHaveConcerns_0      | yes   |
      | action_doYouHaveConcernsDetails | dyhcd |
    And I go back from the step

  Scenario: any other info
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-other_info, start"
     # step 1
    And the step cannot be submitted without making a selection
    Then the step with the following values CANNOT be submitted:
      | more_info_actionMoreInfo_0      | yes |       |
      | more_info_actionMoreInfoDetails |     | [ERR] |
    Then the step with the following values CAN be submitted:
      | more_info_actionMoreInfo_0      | yes  |
      | more_info_actionMoreInfoDetails | amid |
    # check summary page
    And each text should be present in the corresponding region:
      | Yes    | more-info         |
      | amid | more-info-details |
    # edit
    When I click on "edit" in the "more-info" region
    Then the step with the following values CAN be submitted:
      | more_info_actionMoreInfo_1      | no  |
    And each text should be present in the corresponding region:
      | No    | more-info         |
    And I should not see the "more-info-details" region

  Scenario: deputy expenses
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-deputy_expenses, start"
    # chose "no records"
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_paidForAnything_1 | no |
        # summary page check
    And each text should be present in the corresponding region:
      | No | paid-for-anything |
        # select there are records (from summary page link)
    Given I click on "edit" in the "paid-for-anything" region
    And the step with the following values CAN be submitted:
      | yes_no_paidForAnything_0 | yes |
        # add expense n.1 (and validate form)
    And the step with the following values CANNOT be submitted:
      | expenses_single_explanation |  | [ERR] |
      | expenses_single_amount      |  | [ERR] |
    And the step with the following values CANNOT be submitted:
      | expenses_single_explanation |                | [ERR] |
      | expenses_single_amount      | invalid number | [ERR] |
    And the step with the following values CANNOT be submitted:
      | expenses_single_explanation |                | [ERR] |
      | expenses_single_amount      | 0.0 | [ERR] |
    And the step with the following values CAN be submitted:
      | expenses_single_explanation | taxi from hospital on 3 november |
      | expenses_single_amount      | 35                               |
        # add expense n.2
    And I choose "yes" when asked for adding another record
    And the step with the following values CAN be submitted:
      | expenses_single_explanation | food for client on 3 november |
      | expenses_single_amount      | 14                            |
        # add another: no
    And I choose "no" when asked for adding another record
        # check record in summary page
    And each text should be present in the corresponding region:
      | taxi from hospital on 3 november | expense-taxi-from-hospital-on-3-november |
      | £35.00                           | expense-taxi-from-hospital-on-3-november |
      | food for client on 3 november    | expense-food-for-client-on-3-november    |
      | £14.00                           | expense-food-for-client-on-3-november    |
      | £49.00                           | expense-total    |
        # remove expense n.2
    When I click on "delete" in the "expense-food-for-client-on-3-november" region
    Then I should not see the "expense-food-for-client-on-3-november" region
        # test add link
    When I click on "add"
    Then I should see the "save-and-continue" link
    When I go back from the step
        # edit expense n.1
    When I click on "edit" in the "expense-taxi-from-hospital-on-3-november" region
    Then the following fields should have the corresponding values:
      | expenses_single_explanation | taxi from hospital on 3 november |
      | expenses_single_amount      | 35.00                               |
    And the step with the following values CAN be submitted:
      | expenses_single_explanation | taxi from hospital on 4 november |
      | expenses_single_amount      | 45                               |
    And each text should be present in the corresponding region:
      | taxi from hospital on 4 november | expense-taxi-from-hospital-on-4-november |
      | £45.00                           | expense-taxi-from-hospital-on-4-november |

  Scenario: gifts
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-gifts, start"
    # chose "no records"
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_giftsExist_1 | no |
        # summary page check
    And each text should be present in the corresponding region:
      | No | gifts-exist |
        # select there are records (from summary page link)
    Given I click on "edit" in the "gifts-exist" region
    And the step with the following values CAN be submitted:
      | yes_no_giftsExist_0 | yes |
        # add expense n.1 (and validate form)
    And the step with the following values CANNOT be submitted:
      | gifts_single_explanation |  | [ERR] |
      | gifts_single_amount      |  | [ERR] |
    And the step with the following values CANNOT be submitted:
      | gifts_single_explanation |                | [ERR] |
      | gifts_single_amount      | invalid number | [ERR] |
    And the step with the following values CANNOT be submitted:
      | gifts_single_explanation |     | [ERR] |
      | gifts_single_amount      | 0.0 | [ERR] |
    And the step with the following values CAN be submitted:
      | gifts_single_explanation | birthday gift to daughter |
      | gifts_single_amount      | 35                        |
        # add expense n.2
    And I choose "yes" when asked for adding another record
    And the step with the following values CAN be submitted:
      | gifts_single_explanation | gift for the dog |
      | gifts_single_amount      | 14               |
        # add another: no
    And I choose "no" when asked for adding another record
        # check record in summary page
    And each text should be present in the corresponding region:
      | birthday gift to daughter | gift-birthday-gift-to-daughter |
      | £35.00                    | gift-birthday-gift-to-daughter |
      | gift for the dog          | gift-gift-for-the-dog          |
      | £14.00                    | gift-gift-for-the-dog          |
      | £49.00                    | gift-total                     |
        # remove expense n.2
    When I click on "delete" in the "gift-gift-for-the-dog" region
    Then I should not see the "gift-gift-for-the-dog" region
        # test add link
    When I click on "add"
    Then I should see the "save-and-continue" link
    When I go back from the step
        # edit expense n.1
    When I click on "edit" in the "gift-birthday-gift-to-daughter" region
    Then the following fields should have the corresponding values:
      | gifts_single_explanation | birthday gift to daughter |
      | gifts_single_amount      | 35.00                     |
    And the step with the following values CAN be submitted:
      | gifts_single_explanation | birthday gift to the daughter |
      | gifts_single_amount      | 45                            |
    And each text should be present in the corresponding region:
      | birthday gift to the daughter | gift-birthday-gift-to-the-daughter |
      | £45.00                        | gift-birthday-gift-to-the-daughter |

  Scenario: assets
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-assets, start"
      # chose "no records"
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_noAssetToAdd_1 | 1 |
      # summary page check
    And each text should be present in the corresponding region:
      | No      | has-assets      |
      # select there are records (from summary page link)
    Given I click on "edit" in the "has-assets" region
    And the step with the following values CAN be submitted:
      | yes_no_noAssetToAdd_0 | 0 |
      # add asset n.1 Vehicle (and validate form)
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | asset_title_title_0 | Vehicles  |
    And the step with the following values CANNOT be submitted:
      | asset_value |   | [ERR] |
      | asset_description |   | [ERR] |
    And the step with the following values CANNOT be submitted:
      | asset_value       | 1000000000001 |  [ERR] |
      | asset_description | Alfa Romeo 156 JTD |  [OK] |
      | asset_valuationDate_day | 99 |  [ERR] |
      | asset_valuationDate_month |  |  [ERR] |
      | asset_valuationDate_year | 2016 |  [ERR] |
    And the step with the following values CAN be submitted:
      | asset_value       | 17,000 |
      | asset_description | Alfa Romeo 156 JTD |
      | asset_valuationDate_day | 12 |
      | asset_valuationDate_month | 1 |
      | asset_valuationDate_year | 2016 |
      # add asset n.2 Artwork
    And I choose "yes" when asked for adding another record
    And the step with the following values CAN be submitted:
      | asset_title_title_0 | Artwork |
    And the step with the following values CAN be submitted:
      | asset_value       | 25010.00 |
      | asset_description | Impressionist painting |
      | asset_valuationDate_day |  |
      | asset_valuationDate_month |  |
      | asset_valuationDate_year |  |
      # add asset n.3 Artwork (will be deleted)
    And I choose "yes" when asked for adding another record
    And the step with the following values CAN be submitted:
      | asset_title_title_0 | Artwork |
    And the step with the following values CAN be submitted:
      | asset_value       | 999.00 |
      | asset_description | temp |
      | asset_valuationDate_day |  |
      | asset_valuationDate_month |  |
      | asset_valuationDate_year |  |
      #add another: no
    And I choose "no" when asked for adding another record
      # check record in summary page
    And each text should be present in the corresponding region:
      | Alfa Romeo 156 JTD | asset-alfa-romeo-156-jtd |
      | £17,000.00 | asset-alfa-romeo-156-jtd |
      | 12 January 2016 | asset-alfa-romeo-156-jtd |
      | Impressionist painting | asset-impressionist-painting |
      | £25,010.00 | asset-impressionist-painting |
      # remove asset n.3
    When I click on "delete" in the "asset-temp" region
    Then I should not see the "asset-temp" region
      # test add link
    When I click on "add"
    Then I should see the "save-and-continue" link
    When I go back from the step
      # edit asset n.1
    When I click on "edit" in the "asset-alfa-romeo-156-jtd" region
    Then the following fields should have the corresponding values:
      | asset_value       | 17,000.00 |
      | asset_description | Alfa Romeo 156 JTD |
      | asset_valuationDate_day | 12 |
      | asset_valuationDate_month | 01 |
      | asset_valuationDate_year | 2016 |
    And the step with the following values CAN be submitted:
      | asset_value       | 17,500 |
      | asset_description | Alfa Romeo 147 JTD |
      | asset_valuationDate_day | 11 |
      | asset_valuationDate_month | 3 |
      | asset_valuationDate_year | 2015 |
    And each text should be present in the corresponding region:
      | Alfa Romeo 147 JTD | asset-alfa-romeo-147-jtd |
      | £17,500.00 | asset-alfa-romeo-147-jtd |
      | 11 March 2015 | asset-alfa-romeo-147-jtd |

  Scenario: properties
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-assets, add"
    And the step with the following values CAN be submitted:
      | asset_title_title_0 | Property   |
    And the step with the following values CANNOT be submitted:
      | asset_address     |   | [ERR] |
      | asset_address2    |   |       |
      | asset_county      |   |       |
      | asset_postcode    |   | [ERR] |
    And the step with the following values CAN be submitted:
      | asset_address     | 12 gold house  |
      | asset_address2    | mortimer road  |
      | asset_county      |  westminster   |
      | asset_postcode    |  SW11 5TF      |
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | asset_occupants | only the deputy only |
    Then the step cannot be submitted without making a selection
    And the step with the following values CANNOT be submitted:
      | asset_owned_1         | partly |       |
      | asset_ownedPercentage |        | [ERR] |
    And the step with the following values CAN be submitted:
      | asset_owned_1         | partly |
      | asset_ownedPercentage | 50     |
    Then the step cannot be submitted without making a selection
    And the step with the following values CANNOT be submitted:
      | asset_hasMortgage_0             | yes |       |
      | asset_mortgageOutstandingAmount |     | [ERR] |
    And the step with the following values CAN be submitted:
      | asset_hasMortgage_0             | yes    |
      | asset_mortgageOutstandingAmount | 120500 |
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | asset_value             | 241000    |
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | asset_isSubjectToEquityRelease_0 | yes  |
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | asset_hasCharges_1 |  no |
    Then the step cannot be submitted without making a selection
    And the step with the following values CANNOT be submitted:
      | asset_isRentedOut_0             | yes |       |
      | asset_rentAgreementEndDate_month |     | [ERR] |
      | asset_rentAgreementEndDate_year |     | [ERR] |
    And the step with the following values CAN be submitted:
      | asset_isRentedOut_0               | yes     |
      | asset_rentAgreementEndDate_month  |  12     |
      | asset_rentAgreementEndDate_year   |  2017   |
      | asset_rentIncomeMonth             |  1350   |
      #add another: no
    And I choose "no" when asked for adding another record
      # check record in summary page
    And each text should be present in the corresponding region:
      | 12 gold house | property-sw11-5tf-address |
      | SW11 5TF | property-sw11-5tf-address |
      # edit asset n.1
    When I click on "edit" in the "property-sw11-5tf-address" region
    Then the following fields should have the corresponding values:
      | asset_address     | 12 gold house  |
      | asset_address2    | mortimer road  |
      | asset_county      |  westminster   |
      | asset_postcode    |  SW11 5TF      |
    And the step with the following values CAN be submitted:
      | asset_address     | 13 gold house  |
      | asset_postcode    |  SW11 6TF      |
    And each text should be present in the corresponding region:
      | 13 gold house | property-sw11-6tf-address |
      # remove property
    When I save the application status into "pa-report-assets-finished"
    And I click on "delete" in the "property-sw11-6tf" region
    Then I should not see the "property-sw11-6tf" region
    And I load the application status from "pa-report-assets-finished"

  Scenario: debts
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-debts, start"
      # chose "no records"
    Given the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | yes_no_hasDebts_1 | no |
      # summary page check
    And each text should be present in the corresponding region:
      | No      | has-debts       |
      # select there are records (from summary page link)
    Given I click on "edit" in the "has-debts" region
    And the step with the following values CAN be submitted:
      | yes_no_hasDebts_0 | yes |
      # edit debts
    And the step cannot be submitted without making a selection
    And the step with the following values CANNOT be submitted:
      | debt_debts_0_amount |  |
      | debt_debts_1_amount |  |
      | debt_debts_2_amount |  |
      | debt_debts_3_amount |  |
    And the step with the following values CANNOT be submitted:
      | debt_debts_0_amount       | abc                         |   [ERR]   |
      | debt_debts_1_amount       | 76235746253746253746253746  |   [ERR]   |
      | debt_debts_2_amount       | -1                          |   [ERR]   |
      | debt_debts_3_amount       | 1                           |   [OK]   |
      | debt_debts_3_moreDetails  |                             |   [ERR]   |
    And the step with the following values CAN be submitted:
      | debt_debts_0_amount | 12331.234 |
      | debt_debts_1_amount |  |
      | debt_debts_2_amount | 1 |
      | debt_debts_3_amount | 2 |
      | debt_debts_3_moreDetails | mr |
      # check record in summary page
    And each text should be present in the corresponding region:
      | £12,331.23    | debt-care-fees |
      | £0.00         | debt-credit-cards |
      | £1.00         | debt-loans |
      | £2.00         | debt-other |
      | mr            | debt-other-more-details |
      # edit debts again
    When I click on "edit" in the "debts-list" region
    Then the following fields should have the corresponding values:
      | debt_debts_0_amount | 12,331.23 |
      | debt_debts_1_amount |  |
      | debt_debts_2_amount | 1.00 |
      | debt_debts_3_amount | 2.00 |
      | debt_debts_3_moreDetails | mr |
    And the step with the following values CAN be submitted:
      | debt_debts_0_amount | 1 |
      | debt_debts_1_amount | 2 |
      | debt_debts_2_amount | 3 |
      | debt_debts_3_amount | 4 |
      | debt_debts_3_moreDetails | 5 mr |
    And each text should be present in the corresponding region:
      | £1.00 | debt-care-fees |
      | £2.00 | debt-credit-cards |
      | £3.00 | debt-loans |
      | £4.00 | debt-other |
      | 5 mr  | debt-other-more-details |

  Scenario: add account
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-bank_accounts, start"
    # step 1
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | account_accountType_0 | current |
    # add account n.1 (current)
    Then the step cannot be submitted without making a selection
    And the step with the following values CANNOT be submitted:
      | account_bank                      | x | [ERR] |
      | account_accountNumber             | x | [ERR] |
      | account_sortCode_sort_code_part_1 | g | [ERR] |
      | account_sortCode_sort_code_part_2 | h | [ERR] |
      | account_sortCode_sort_code_part_3 |   | [ERR] |
    And the step with the following values CAN be submitted:
      | account_bank                      | HSBC - main account |
      | account_accountNumber             | 01ca                |
      | account_sortCode_sort_code_part_1 | 11                  |
      | account_sortCode_sort_code_part_2 | 22                  |
      | account_sortCode_sort_code_part_3 | 33                  |
      | account_isJointAccount_1          | no                  |
    And the step with the following values CANNOT be submitted:
      | account_openingBalance | invalid | [ERR] |
      | account_closingBalance | invalid | [ERR] |
    And the step with the following values CAN be submitted:
      | account_openingBalance | 100.40 |
      | account_closingBalance | 200.50 |
    # add another: yes
    And I choose "yes" when asked for adding another record
    # add account n.2 (cfo)
    And the step with the following values CAN be submitted:
      | account_accountType_0 | cfo |
    And the step with the following values CAN be submitted:
      | account_accountNumber    | 11cf |
      | account_isJointAccount_1 | no   |
    And the step with the following values CAN be submitted:
      | account_openingBalance | 234 |
      | account_closingBalance | 235 |
    # add another: yes
    And I choose "yes" when asked for adding another record
    # add account n.3 (temp)
    And the step with the following values CAN be submitted:
      | account_accountType_0 | current |
    And the step with the following values CAN be submitted:
      | account_bank                      | temp2 |
      | account_accountNumber             | temp  |
      | account_sortCode_sort_code_part_1 | 33    |
      | account_sortCode_sort_code_part_2 | 33    |
      | account_sortCode_sort_code_part_3 | 33    |
      | account_isJointAccount_1          | no    |
    And the step with the following values CAN be submitted:
      | account_openingBalance | 123 |
      | account_closingBalance | 123 |
    # add another: no
    And I choose "no" when asked for adding another record
    # check record in summary page
    And each text should be present in the corresponding region:
      | HSBC - main account        | account-01ca |
      | Current account            | account-01ca |
      | 112233                     | account-01ca |
      | £100.40                    | account-01ca |
      | £200.50                    | account-01ca |
      | Court funds office account | account-11cf |
      | £234.00                    | account-11cf |
      | £235.00                    | account-11cf |
    # remove account
    When I click on "delete" in the "account-temp" region
    Then I should not see the "account-temp" region
    # test add link
    When I click on "add"
    Then I should see the "save-and-continue" link
    When I go back from the step
    # edit account n.1
    When I click on "edit" in the "account-01ca" region
    Then the following fields should have the corresponding values:
      | account_accountType_0 | current |
    And the step with the following values CAN be submitted:
      | account_accountType_0 | savings |
    Then the following fields should have the corresponding values:
      | account_bank                      | HSBC - main account |
      | account_accountNumber             | 01ca                |
      | account_sortCode_sort_code_part_1 | 11                  |
      | account_sortCode_sort_code_part_2 | 22                  |
      | account_sortCode_sort_code_part_3 | 33                  |
      | account_isJointAccount_1          | no                  |
    And the step with the following values CAN be submitted:
      | account_bank                      | HSBC - saving account |
      | account_accountNumber             | 02ca                  |
      | account_sortCode_sort_code_part_1 | 44                    |
      | account_sortCode_sort_code_part_2 | 55                    |
      | account_sortCode_sort_code_part_3 | 66                    |
      | account_isJointAccount_0          | yes                   |
    Then the following fields should have the corresponding values:
      | account_openingBalance | 100.40 |
      | account_closingBalance | 200.50 |
    And the step with the following values CAN be submitted:
      | account_openingBalance | 101.40 |
      | account_closingBalance | 201.50 |
    And each text should be present in the corresponding region:
      | HSBC - saving account | account-02ca |
      | Saving account        | account-02ca |
      | 445566                | account-02ca |
      | £101.40               | account-02ca |
      | £201.50               | account-02ca |
    Given I save the application status into "pa-temp"

  Scenario: money in 102
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-money_in, start"
    # add transaction n.1 and check validation
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | account_group_0 | pensions |
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | account_category_0 | state-pension |
    And the step with the following values CANNOT be submitted:
      | account_description |  |       |
      | account_amount      |  | [ERR] |
    And the step with the following values CANNOT be submitted:
      | account_description |  | 0   |
      | account_amount      |  | [ERR] |
    And the step with the following values CAN be submitted:
      | account_description | pension received |
      | account_amount      | 12345.67         |
    # add another: yes
    And I choose "yes" when asked for adding another record
    # add transaction n.2
    And the step with the following values CAN be submitted:
      | account_group_0 | pensions |
    And the step with the following values CAN be submitted:
      | account_category_0 | state-pension |
    And the step with the following values CAN be submitted:
      | account_description | delete me |
      | account_amount      | 1         |
    # add another: yes
    And I choose "yes" when asked for adding another record
    # add transaction n.3
    And the step with the following values CAN be submitted:
      | account_group_0 | moneyin-other |
    And the step with the following values CAN be submitted:
      | account_category_0 | anything-else |
    And the step with the following values CAN be submitted:
      | account_description | money found on the road |
      | account_amount      | 50                      |
    # add another: no
    And I choose "no" when asked for adding another record
    # check record in summary page
    And each text should be present in the corresponding region:
      | State Pension           | transaction-pension-received        |
      | pension received        | transaction-pension-received        |
      | £12,345.67              | transaction-pension-received        |
      | State Pension           | transaction-delete-me               |
      | delete me               | transaction-delete-me               |
      | £1                      | transaction-delete-me               |
      | Anything else           | transaction-money-found-on-the-road |
      | money found on the road | transaction-money-found-on-the-road |
      | £50.00                  | transaction-money-found-on-the-road |
      | £12,346.67              | pensions-total                      |
    # remove transaction n.2
    When I click on "delete" in the "transaction-delete-me" region
    Then I should not see the "transaction-delete-me" region
    # test add link
    When I click on "add"
    Then I should see the "save-and-continue" link
    When I go back from the step
    # edit transaction n.3
    When I click on "edit" in the "transaction-money-found-on-the-road" region
    Then the following fields should have the corresponding values:
      | account_description | money found on the road |
      | account_amount      | 50.00                      |
    And the step with the following values CAN be submitted:
      | account_description | Some money found on the road |
      | account_amount      | 51                      |
    And each text should be present in the corresponding region:
      | Anything else           | transaction-some-money-found-on-the-road |
      | Some money found on the road | transaction-some-money-found-on-the-road |
      | £51.00 | transaction-some-money-found-on-the-road |

  Scenario: money out
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-money_out, start"
      # add transaction n.1 and check validation
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | account_group_0 | household-bills |
    Then the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | account_category_0 | broadband |
    And the step with the following values CANNOT be submitted:
      | account_description |  |       |
      | account_amount      |  | [ERR] |
    And the step with the following values CANNOT be submitted:
      | account_description |  | 0     |
      | account_amount      |  | [ERR] |
    And the step with the following values CAN be submitted:
      | account_description | january bill |
      | account_amount      | 12345.68     |
      # add another: yes
    And I choose "yes" when asked for adding another record
      # add transaction n.2
    And the step with the following values CAN be submitted:
      | account_group_0 | household-bills |
    And the step with the following values CAN be submitted:
      | account_category_0 | broadband |
    And the step with the following values CAN be submitted:
      | account_description | delete me |
      | account_amount      | 1         |
      # add another: yes
    And I choose "yes" when asked for adding another record
      # add transaction n.3
    And the step with the following values CAN be submitted:
      | account_group_0 | moneyout-other |
    And the step with the following values CAN be submitted:
      | account_category_0 | anything-else-paid-out |
    And the step with the following values CAN be submitted:
      | account_description | money found on the road |
      | account_amount      | 50                      |
      # add another: no
    And I choose "no" when asked for adding another record
      # check record in summary page
    And each text should be present in the corresponding region:
      | Broadband               | transaction-january-bill            |
      | january bill            | transaction-january-bill            |
      | £12,345.68              | transaction-january-bill            |
      | Broadband               | transaction-delete-me               |
      | delete me               | transaction-delete-me               |
      | £1                      | transaction-delete-me               |
      | Anything else           | transaction-money-found-on-the-road |
      | money found on the road | transaction-money-found-on-the-road |
      | £50.00                  | transaction-money-found-on-the-road |
      | £12,346.68              | household-bills-total               |
      # remove transaction n.2
    When I click on "delete" in the "transaction-delete-me" region
    Then I should not see the "transaction-delete-me" region
      # test add link
    When I click on "add"
    Then I should see the "save-and-continue" link
    When I go back from the step
      # edit transaction n.3
    When I click on "edit" in the "transaction-money-found-on-the-road" region
    Then the following fields should have the corresponding values:
      | account_description | money found on the road |
      | account_amount      | 50.00                   |
    And the step with the following values CAN be submitted:
      | account_description | Some money found on the road |
      | account_amount      | 51                           |
    And each text should be present in the corresponding region:
      | Anything else                | transaction-some-money-found-on-the-road |
      | Some money found on the road | transaction-some-money-found-on-the-road |
      | £51.00                       | transaction-some-money-found-on-the-road |

  Scenario: transfers
    Given I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "edit-money_transfers, start"
      # chose "no records"
    Given the step cannot be submitted without making a selection
    Then the step with the following values CAN be submitted:
      | yes_no_noTransfersToAdd_1 | 1 |
      # summary page check
    And each text should be present in the corresponding region:
      | No | no-transfers-to-add |
      # select there are records (from summary page link)
    Given I click on "edit" in the "no-transfers-to-add" region
    Then the step with the following values CAN be submitted:
      | yes_no_noTransfersToAdd_0 | 0 |
      # add transfer n.1 (and validate form)
    And the step cannot be submitted without making a selection
    And the step with the following values CAN be submitted:
      | money_transfers_type_accountFromId | 1 |
      | money_transfers_type_accountToId   | 2 |
    And the step cannot be submitted without making a selection
    And the step with the following values CANNOT be submitted:
      | money_transfers_type_amount | asasd |
    And the step with the following values CAN be submitted:
      | money_transfers_type_amount | 1234.56 |
      # add another: yes
    And I choose "yes" when asked for adding another record
      # add transfer n.2
    And the step with the following values CAN be submitted:
      | money_transfers_type_accountFromId | 1 |
      | money_transfers_type_accountToId   | 2 |
    And the step with the following values CAN be submitted:
      | money_transfers_type_amount | 98.76 |
      # add another: no
    And I choose "no" when asked for adding another record
    #check record in summary page
    And each text should be present in the corresponding region:
      | £1,234.56 | transfer-02ca-11cf-123456 |
      | £98.76    | transfer-02ca-11cf-9876   |
      # remove transfer n.2
    When I click on "delete" in the "transfer-02ca-11cf-9876" region
    Then I should not see the "transfer-02ca-11cf-9876" region
      # test add link
    When I click on "add"
    Then I should see the "save-and-continue" link
    When I go back from the step
      # edit transfer n.1
    When I click on "edit" in the "transfer-02ca-11cf-123456" region
    Then the following fields should have the corresponding values:
      | money_transfers_type_accountFromId | 1 |
      | money_transfers_type_accountToId   | 2 |
    And the step with the following values CAN be submitted:
      | money_transfers_type_accountFromId | 2 |
      | money_transfers_type_accountToId   | 1 |
    Then the following fields should have the corresponding values:
      | money_transfers_type_amount | 1,234.56 |
    And the step with the following values CAN be submitted:
      | money_transfers_type_amount | 1,234.57 |
    And each text should be present in the corresponding region:
      | £1,234.57 | transfer-11cf-02ca-123457 |

  Scenario: balance fix
    Given I save the application status into "pa-report-balance-before"
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
      # assert report not submittable
    And I click on "pa-report-open" in the "client-1000014" region
    Then the report should not be submittable
      # check balance mismatch difference
    When I click on "balance-view-details"
    Then I should see the "balance-bad" region
    And I should see "£191.11" in the "unaccounted-for" region
      # fix balance
    And I save the application status into "pa-balance-before-adding-explanation"
    And I click on "step-back, edit-bank_accounts"
    And I click on "edit" in the "account-11cf" region
    And I submit the step
    And I submit the step
    And the step with the following values CAN be submitted:
      | account_closingBalance | 43.89 |
    And I click on "breadcrumbs-report-overview"
      # assert balance is now good
    Then I should not see the "balance-bad" region
      # assert report can be sumbmitted
      # When I set the report 1 end date to 3 days ago
    Then the report should be submittable

  Scenario: balance explanation
      # restore previous bad balance, add explanation
    Given I save the application status into "pa-report-balance-explanation-before"
    And I load the application status from "pa-balance-before-adding-explanation"
    And I am logged in as "behat-pa1@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "pa-report-open" in the "client-1000014" region
    And I click on "balance-view-details"
    And I should see the "balance-bad" region
      # add explanation
    Then the step cannot be submitted without making a selection
    And the step with the following values CANNOT be submitted:
      | balance_balanceMismatchExplanation    | short | [ERR] |
    And the step with the following values CAN be submitted:
      | balance_balanceMismatchExplanation    | lost 110 pounds on the road |
    And I should not see the "balance-view-details" link
    And the report should be submittable