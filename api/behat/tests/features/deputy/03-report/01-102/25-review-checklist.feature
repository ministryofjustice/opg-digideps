Feature: Full review checklist

  @deputy
  Scenario: Full review checklist contains logic summary
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I open the "2016" checklist for client "102"
    Then I should see the "lodging-summary" region
    And I should see "I am not satisfied" in the "lodging-summary" region
    And I should see "I am referring the case for a staff review" in the "lodging-summary" region
    And I should see "Some more info 1" in the "lodging-summary" region
    And I should see "Some more info 2" in the "lodging-summary" region

  @deputy
  Scenario: Full review checklist requires validation on submission
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I open the "2016" checklist for client "102"
    And I click on "submit-and-continue" in the "full-review-checklist" region
    Then the following fields should have an error:
        | full-review_answers_fullBankStatementsExist_0 |
        | full-review_answers_fullBankStatementsExist_1 |
        | full-review_answers_fullBankStatementsExist_2 |
        | full-review_answers_anyLodgingConcerns_0 |
        | full-review_answers_anyLodgingConcerns_1 |
        | full-review_answers_anyLodgingConcerns_2 |
        | full-review_answers_spendingAcceptable_0 |
        | full-review_answers_spendingAcceptable_1 |
        | full-review_answers_spendingAcceptable_2 |
        | full-review_answers_expensesReasonable_0 |
        | full-review_answers_expensesReasonable_1 |
        | full-review_answers_expensesReasonable_2 |
        | full-review_answers_giftingReasonable_0 |
        | full-review_answers_giftingReasonable_1 |
        | full-review_answers_giftingReasonable_2 |
        | full-review_answers_debtManageable_0 |
        | full-review_answers_debtManageable_1 |
        | full-review_answers_debtManageable_2 |
        | full-review_answers_anySpendingConcerns_0 |
        | full-review_answers_anySpendingConcerns_1 |
        | full-review_answers_anySpendingConcerns_2 |
        | full-review_answers_needReferral_0 |
        | full-review_answers_needReferral_1 |
        | full-review_decision_0 |
        | full-review_decision_1 |
        | full-review_decision_2 |
    When I click on "save-progress" in the "full-review-checklist" region
    Then the form should be valid
    And I should see "Admin User, Admin" in the "fullReview-last-saved-by" region

  @deputy
  Scenario: Can add details to the full review checklist
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I open the "2016" checklist for client "102"
    And I fill in the following:
        | full-review_answers_fullBankStatementsExist_0 | yes                         |
        | full-review_answers_anyLodgingConcerns_1      | no                          |
        | full-review_answers_spendingAcceptable_2      | na                          |
        | full-review_answers_expensesReasonable_1      | no                          |
        | full-review_answers_giftingReasonable_0       | yes                         |
        | full-review_answers_debtManageable_0          | yes                         |
        | full-review_answers_anySpendingConcerns_1     | no                          |
        | full-review_answers_needReferral_0            | yes                         |
        | full-review_decision_0                        | satisfied                   |
        | full-review_answers_decisionExplanation       | I am happy with this report |
    And I click on "save-progress" in the "full-review-checklist" region
    Then the form should be valid
    And the following fields should have the corresponding values:
        | full-review_answers_fullBankStatementsExist_0 | yes                         |
        | full-review_answers_anyLodgingConcerns_1      | no                          |
        | full-review_answers_spendingAcceptable_2      | na                          |
        | full-review_answers_expensesReasonable_1      | no                          |
        | full-review_answers_giftingReasonable_0       | yes                         |
        | full-review_answers_debtManageable_0          | yes                         |
        | full-review_answers_anySpendingConcerns_1     | no                          |
        | full-review_answers_needReferral_0            | yes                         |
        | full-review_decision_0                        | satisfied                   |
        | full-review_answers_decisionExplanation       | I am happy with this report |

  @deputy
  Scenario: Can submit full review checklist
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "DigidepsPass1234"
    When I open the "2016" checklist for client "102"
    And I click on "submit-and-continue" in the "full-review-checklist" region
    Then the form should be valid
