<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait DeputyCostsEstimateSectionTrait
{
    private string $totalAmountLessThanSumOfOtherAmountsError = 'The individual breakdown of costs must not exceed the total general management amount';
    private string $missingAmountError = 'Please enter an amount';
    private string $noOptionSelectedError = 'Please select an option';
    private string $yesNoNotSelectedError = 'Please select either \'Yes\' or \'No\'';

    /**
     * @When I navigate to and start the deputy costs estimates report section
     */
    public function iNavigateToAndStartDeputyCostsEstimatesSection()
    {
        $this->iVisitReportOverviewPage();
        $this->iAmOnReportsOverviewPage();
        $this->clickLink('edit-prof_deputy_costs_estimate');
        $this->iAmOnDeputyCostsEstimateStartPage();
        $this->clickLink('Start');
    }

    /**
     * @When I choose fixed costs
     */
    public function iChooseFixedCosts()
    {
        $this->iAmOnDeputyCostsEstimateChargesPage();
        $this->chooseOption('deputy_costs_estimate[profDeputyCostsEstimateHowCharged]', 'fixed', 'deputyCostType', 'fixed costs');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I choose assessed costs
     */
    public function iChooseAssessedCosts()
    {
        $this->iAmOnDeputyCostsEstimateChargesPage();
        $this->chooseOption('deputy_costs_estimate[profDeputyCostsEstimateHowCharged]', 'assessed', 'deputyCostType', 'assessed costs');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I choose fixed and assessed costs
     */
    public function iChooseFixedAndAssessedCosts()
    {
        $this->iAmOnDeputyCostsEstimateChargesPage();
        $this->chooseOption('deputy_costs_estimate[profDeputyCostsEstimateHowCharged]', 'both', 'deputyCostType', 'both fixed and assessed costs');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I check that if I choose nothing for costs type
     */
    public function iChooseNothingForCostsType()
    {
        $this->iAmOnDeputyCostsEstimateChargesPage();
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I get a cost type validation error
     */
    public function iGetCostsTypeValidationError()
    {
        $this->iAmOnDeputyCostsEstimateChargesPage();
        $this->assertOnAlertMessage($this->noOptionSelectedError);
        $this->chooseOption('deputy_costs_estimate[profDeputyCostsEstimateHowCharged]', 'assessed', 'deputyCostType', 'assessed costs');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I fill in the estimated assessed amounts correctly
     */
    public function iFillInEstimatedAssessedCostsCorrectly()
    {
        $this->iAmOnDeputyCostsEstimateBreakdownPage();
        $this->fillInField('deputy_estimate_costs[profDeputyManagementCostAmount]', 1234, 'managementTotalCost');
        $this->fillInField('deputy_estimate_costs[profDeputyEstimateCosts][0][amount]', 111, 'managementCosts');
        $this->fillInField('deputy_estimate_costs[profDeputyEstimateCosts][1][amount]', 112, 'managementCosts');
        $this->fillInField('deputy_estimate_costs[profDeputyEstimateCosts][2][amount]', 113, 'managementCosts');
        $this->fillInField('deputy_estimate_costs[profDeputyEstimateCosts][3][amount]', 114, 'managementCosts');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I fill in the estimated fixed and assessed costs correctly
     */
    public function iFillInEstimatedFixedAssessedCostsCorrectly()
    {
        $this->iAmOnDeputyCostsEstimateBreakdownPage();
        $this->fillInField('deputy_estimate_costs[profDeputyManagementCostAmount]', 2789, 'managementTotalCost');
        $this->fillInField('deputy_estimate_costs[profDeputyEstimateCosts][0][amount]', 651, 'managementCosts');
        $this->fillInField('deputy_estimate_costs[profDeputyEstimateCosts][1][amount]', 652, 'managementCosts');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I enter invalid values for expected costs
     */
    public function iEnterInvalidValuesForExpectedCosts()
    {
        $this->iAmOnDeputyCostsEstimateBreakdownPage();
        $this->pressButton('Save and continue');
        $this->assertOnAlertMessage($this->missingAmountError);
        $this->fillInField('deputy_estimate_costs[profDeputyManagementCostAmount]', 100, 'managementTotalCost');
        $this->fillInField('deputy_estimate_costs[profDeputyEstimateCosts][0][amount]', 651, 'managementCosts');
        $this->fillInField('deputy_estimate_costs[profDeputyEstimateCosts][1][amount]', 652, 'managementCosts');
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I get an expected costs validation error
     */
    public function iGetExpectedCostsValidationError()
    {
        $this->iAmOnDeputyCostsEstimateBreakdownPage();
        $this->assertOnAlertMessage($this->totalAmountLessThanSumOfOtherAmountsError);
        $this->fillInField('deputy_estimate_costs[profDeputyManagementCostAmount]', 2999, 'managementTotalCost');
        $this->fillInField('deputy_estimate_costs[profDeputyEstimateCosts][0][amount]', 651, 'managementCosts');
        $this->fillInField('deputy_estimate_costs[profDeputyEstimateCosts][1][amount]', 652, 'managementCosts');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I have information that would explain these estimated costs
     */
    public function iHaveInformationThatWouldExplainEstimatedCosts()
    {
        $this->iAmOnDeputyCostsEstimateMoreInfoPage();
        $this->selectOption('deputy_costs_estimate[profDeputyCostsEstimateHasMoreInfo]', 'yes');
        $this->fillInField('deputy_costs_estimate[profDeputyCostsEstimateMoreInfoDetails]', $this->faker->sentence(12), 'moreInfo');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I do not have information that would explain these estimated costs
     */
    public function iHaveNoInformationThatWouldExplainEstimatedCosts()
    {
        $this->iAmOnDeputyCostsEstimateMoreInfoPage();
        $this->chooseOption(
            'deputy_costs_estimate[profDeputyCostsEstimateHasMoreInfo]',
            'no',
            'moreInfo',
            'no more information to add'
        );
        $this->pressButton('Save and continue');
    }

    /**
     * @When I do not enter an option to explain estimated costs
     */
    public function iDoNotEnterOptionToExplainEstimatedCosts()
    {
        $this->iAmOnDeputyCostsEstimateMoreInfoPage();
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I get an explain estimated costs validation error
     */
    public function iGetAnExplainEstimatedCostsValidationError()
    {
        $this->iAmOnDeputyCostsEstimateMoreInfoPage();
        $this->assertOnAlertMessage($this->yesNoNotSelectedError);
    }

    /**
     * @When I follow link to deputy costs estimate
     */
    public function iFollowLinkToDeputyCostsEstimate()
    {
        $this->iAmOnReportsOverviewPage();
        $urlRegex = '/report\/.*\/prof-deputy-costs-estimate$/';
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
    }

    /**
     * @When I edit how i will charge for my services
     */
    public function iEditHowIWillChargeForMyServices()
    {
        $this->iAmOnDeputyCostsEstimateSummaryPage();
        $formSectionName = 'deputyCostType';
        $field = 'deputy_costs_estimate[profDeputyCostsEstimateHowCharged]';
        $value = 'fixed costs';
        $this->addToSubmittedAnswersByFormSections($formSectionName, $field, $value);

        $selector = $this->getLinkBasedOnResponse($this->getSectionAnswers($formSectionName)[0][$field], 'edit');

        $this->editSelectAnswerInSection($selector, $field, 'both', $formSectionName, 'both fixed and assessed costs');
        $this->iFillInEstimatedAssessedCostsCorrectly();
        $this->iHaveInformationThatWouldExplainEstimatedCosts();
    }

    /**
     * @When I edit how much I expect to charge
     */
    public function iEditHowMuchIExpectToCharge()
    {
        $this->iAmOnDeputyCostsEstimateSummaryPage();
        $formSectionName = 'managementTotalCost';
        $field = 'deputy_estimate_costs[profDeputyManagementCostAmount]';

        $answer = $this->moneyFormat($this->getSectionAnswers($formSectionName)[0][$field]);

        $selector = $this->getLinkBasedOnResponse($answer, 'edit');
        $this->editAnswerInSection($selector, $field, 2789, $formSectionName);
    }

    /**
     * @When I edit information that will explain expected costs
     */
    public function iEditInformationThatExplainsExpectedCosts()
    {
        $this->iAmOnDeputyCostsEstimateSummaryPage();
        $formSectionName = 'moreInfo';
        $field = 'deputy_costs_estimate[profDeputyCostsEstimateMoreInfoDetails]';
        $selector = $this->getLinkBasedOnResponse($this->getSectionAnswers($formSectionName)[0][$field], 'edit');
        $this->editAnswerInSection($selector, $field, 'edited details', $formSectionName);
    }

    /**
     * @When I edit the costs breakdown
     */
    public function iEditCostsBreakdown()
    {
        $this->iAmOnDeputyCostsEstimateSummaryPage();
        $formSectionName = 'managementCosts';
        $field = 'deputy_estimate_costs[profDeputyEstimateCosts][0][amount]';
        $answer = $this->moneyFormat($this->getSectionAnswers($formSectionName)[0][$field]);
        $selector = $this->getLinkBasedOnResponse($answer, 'edit');
        $this->editAnswerInSection($selector, $field, 222, $formSectionName, false);
    }

    /**
     * @Then the deputy costs estimate summary page should contain the details I entered
     */
    public function deputyCostsEstimateSummaryPageContainsEnteredDetails()
    {
        $this->iAmOnDeputyCostsEstimateSummaryPage();

        $this->expectedResultsDisplayedSimplified('deputyCostType');

        if (array_key_exists('managementCosts', $this->submittedAnswersByFormSections)) {
            $this->expectedResultsDisplayedSimplified('managementCosts');
        }
        if (array_key_exists('moreInfo', $this->submittedAnswersByFormSections)) {
            $this->expectedResultsDisplayedSimplified('moreInfo');
        }
        if (array_key_exists('managementTotalCost', $this->submittedAnswersByFormSections)) {
            $this->expectedResultsDisplayedSimplified('managementTotalCost');
        }
    }
}
