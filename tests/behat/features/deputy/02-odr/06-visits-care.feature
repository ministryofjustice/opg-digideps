Feature: odr / visits care

  @odr
  Scenario: ODR visits care
    Given I am logged in as "behat-user-odr@publicguardian.gsi.gov.uk" with password "Abcd1234"
    And I click on "odr-start, edit-visitsCare"
    And I save the page as "odr-visits-care-empty"
    # empty form
    When I press "odr_visits_care_save"
    Then the following fields should have an error:
      | odr_visits_care_planMoveNewResidence_0      |
      | odr_visits_care_planMoveNewResidence_1      |
      | odr_visits_care_doYouLiveWithClient_0       |
      | odr_visits_care_doYouLiveWithClient_1       |
      | odr_visits_care_doesClientReceivePaidCare_0 |
      | odr_visits_care_doesClientReceivePaidCare_1 |
      | odr_visits_care_whoIsDoingTheCaring         |
      | odr_visits_care_doesClientHaveACarePlan_0   |
      | odr_visits_care_doesClientHaveACarePlan_1   |
    # missing details on textareas
    When I fill in the following:
      | odr_visits_care_planMoveNewResidence_0      | yes        |
      | odr_visits_care_doYouLiveWithClient_1       | no         |
      | odr_visits_care_doesClientReceivePaidCare_0 | yes        |
      | odr_visits_care_howOftenDoYouContactClient  |            |
      | odr_visits_care_whoIsDoingTheCaring         | Fred Jones |
      | odr_visits_care_doesClientHaveACarePlan_0   | yes        |
    And I press "odr_visits_care_save"
    Then the following fields should have an error:
      | odr_visits_care_planMoveNewResidenceDetails       |
      | odr_visits_care_howOftenDoYouContactClient        |
      | odr_visits_care_howIsCareFunded_0                 |
      | odr_visits_care_howIsCareFunded_1                 |
      | odr_visits_care_howIsCareFunded_2                 |
      | odr_visits_care_whenWasCarePlanLastReviewed_month |
      | odr_visits_care_whenWasCarePlanLastReviewed_year  |
    # ok (simple version, no textareas)
    When I fill in the following:
      | odr_visits_care_planMoveNewResidence_1      | no         |
      | odr_visits_care_doYouLiveWithClient_1       | no         |
      | odr_visits_care_howOftenDoYouContactClient  | daily      |
      | odr_visits_care_doesClientReceivePaidCare_1 | no         |
      | odr_visits_care_whoIsDoingTheCaring         | Fred Jones |
      | odr_visits_care_doesClientHaveACarePlan_1   | no         |
    And I press "odr_visits_care_save"
    And the form should be valid
    #check
    When I go to "/"
    And I click on "odr-start, edit-visitsCare"
    Then the following fields should have the corresponding values:
      | odr_visits_care_planMoveNewResidence_1      | no         |
      | odr_visits_care_doYouLiveWithClient_1       | no         |
      | odr_visits_care_howOftenDoYouContactClient  | daily      |
      | odr_visits_care_doesClientReceivePaidCare_1 | no         |
      | odr_visits_care_whoIsDoingTheCaring         | Fred Jones |
      | odr_visits_care_doesClientHaveACarePlan_1   | no         |
    # edit (including textareas)
    When I fill in the following:
      | odr_visits_care_planMoveNewResidence_0            | yes                 |
      | odr_visits_care_planMoveNewResidenceDetails       | pmnrd               |
      | odr_visits_care_doYouLiveWithClient_1             | no                  |
      | odr_visits_care_howOftenDoYouContactClient        | hodycc              |
      | odr_visits_care_doesClientReceivePaidCare_0       | yes                 |
      | odr_visits_care_howIsCareFunded_0                 | client_pays_for_all |
      | odr_visits_care_whoIsDoingTheCaring               | Fred Jones          |
      | odr_visits_care_doesClientHaveACarePlan_0         | yes                 |
      | odr_visits_care_whenWasCarePlanLastReviewed_month | 12                  |
      | odr_visits_care_whenWasCarePlanLastReviewed_year  | 2014                |
    And I press "odr_visits_care_save"
    And the form should be valid
    # check
    When I go to "/"
    And I click on "odr-start, edit-visitsCare"
    Then the following fields should have the corresponding values:
      | odr_visits_care_planMoveNewResidence_0            | yes                 |
      | odr_visits_care_planMoveNewResidenceDetails       | pmnrd               |
      | odr_visits_care_doYouLiveWithClient_1             | no                  |
      | odr_visits_care_howOftenDoYouContactClient        | hodycc              |
      | odr_visits_care_doesClientReceivePaidCare_0       | yes                 |
      | odr_visits_care_howIsCareFunded_0                 | client_pays_for_all |
      | odr_visits_care_whoIsDoingTheCaring               | Fred Jones          |
      | odr_visits_care_doesClientHaveACarePlan_0         | yes                 |
      | odr_visits_care_whenWasCarePlanLastReviewed_month | 12                  |
      | odr_visits_care_whenWasCarePlanLastReviewed_year  | 2014                |