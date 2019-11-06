Feature: Full review checklist

  @deputy
  Scenario: Full review checklist requires validation on submission
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    When I open the "2016" checklist for client "102"
    And I click on "submit" in the "full-review-checklist" region
    Then the following fields should have an error:
        | full-review_decision_0 |
        | full-review_decision_1 |
        | full-review_decision_2 |
    When I click on "save-progress" in the "full-review-checklist" region
    Then the form should be valid
    And I should see "Admin User, OPG Admin" in the "fullReview-last-saved-by" region

  @deputy
  Scenario: Can add details to the full review checklist
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    When I open the "2016" checklist for client "102"
    And I fill in the following:
        | full-review_decision_0                  | satisfied                   |
        | full-review_answers_decisionExplanation | I am happy with this report |
    And I click on "save-progress" in the "full-review-checklist" region
    Then the form should be valid
    And the following fields should have the corresponding values:
        | full-review_decision_0                  | satisfied                   |
        | full-review_answers_decisionExplanation | I am happy with this report |

  @deputy
  Scenario: Can submit full review checklist
    Given I am logged in to admin as "admin@publicguardian.gov.uk" with password "Abcd1234"
    When I open the "2016" checklist for client "102"
    And I click on "submit" in the "full-review-checklist" region
    Then the form should be valid
    And each text should be present in the corresponding region:
      | Admin User, OPG Admin | fullReview-last-saved-by     |
      | Admin User, OPG Admin | fullReview-last-submitted-by |
