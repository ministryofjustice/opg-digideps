<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use App\Tests\Behat\BehatException;

trait DeputyCostsSectionTrait
{
    private string $missingCostTypeError = 'Please select an option';
    private string $missingPreviousCostsIncurredError = 'Please select either \'Yes\' or \'No\'';
    private string $emptyPreviousCostStartDateError = 'Please enter the start date';
    private string $emptyPreviousCostEndDateError = 'Please enter the end date';
    private string $missingValueError = 'Please enter a value';
    private string $endDateBeforeStartDateError = 'Check the end date: it cannot be before the start date';
    private string $amountRangeLimitError = 'The amount must be between £0.01 and £100,000,000,000';
    private string $missingFixedCostAmountError = 'Please enter an amount. Enter 0 if you have not received any payments for this reporting period';
    private string $missingDateError = 'Please enter a date';
    private string $missingSccoAssessedCostError = 'Please enter an amount. Enter 0 if you are not requesting an SCCO assessment';
    private string $negativeSccoAssessedCostError = 'Please enter a positive amount';
    private string $additionalCostNegativeAmountError = 'The amount must be between £0 and £100,000,000,000';
    private string $additionalCostTooLargeAmountError = 'The amount must be between £0 and £100,000,000,000';
    private string $additionalCostMissingOtherDescriptionError = 'Please give us some more information';
    private string $missingInterimCostError = 'Add at least one interim cost';

    /**
     * @When I navigate to and start the deputy costs report section for an existing client
     */
    public function iNavigateToAndStartDeputyCostsExistingClient()
    {
        if (is_null($this->loggedInUserDetails->getClientId())) {
            throw new BehatException('The logged in user does not have a client associated with them. Try again with a user that has a client.');
        }

        $this->iVisitOrgDashboard();

        $clientName = sprintf(
            '%s, %s',
            $this->loggedInUserDetails->getClientLastName(),
            $this->loggedInUserDetails->getClientFirstName(),
        );

        $this->fillInField('search', $this->loggedInUserDetails->getClientLastName());
        $this->pressButton('search_submit');

        $this->clickLink($clientName);
        $this->clickLink('Deputy costs');
        $this->clickLink('Start');
    }

    /**
     * @When I have fixed deputy costs to declare
     */
    public function iHaveFixedDeputyCosts()
    {
        $this->iAmOnDeputyCostsHowChargedPage();

        $this->chooseOption(
            'deputy_costs[profDeputyCostsHowCharged]',
            'fixed',
            'TypeOfCosts',
            'Fixed costs'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When my client has not paid me in the current reporting period for work from a previous period
     */
    public function clientHasNotPaidPreviousCostsInCurrentPeriod()
    {
        $this->iAmOnDeputyCostsPreviousReceivedExistsPage();

        $this->chooseOption(
            'yes_no[profDeputyCostsHasPrevious]',
            'no',
            'HasPreviousCosts'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When my client has paid me in the current reporting period for work from a previous period
     */
    public function clientHasPaidPreviousCostsInCurrentPeriod()
    {
        $this->iAmOnDeputyCostsPreviousReceivedExistsPage();

        $this->chooseOption(
            'yes_no[profDeputyCostsHasPrevious]',
            'yes',
            'HasPreviousCosts'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I enter a valid amount for the current reporting period costs
     */
    public function iEnterValidCurrentCosts()
    {
        $this->iAmOnDeputyCostsCostsReceievedPage();

        $this->fillInFieldTrackTotal(
            'deputy_costs_received[profDeputyFixedCost]',
            588,
            'CurrentPeriodFixedCosts'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I have no additional costs to declare for the current reporting period
     */
    public function iHaveNoAdditionalCosts()
    {
        $this->iAmOnDeputyCostsBreakdownPage();

        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see the expected responses on the deputy costs summary page
     */
    public function iShouldSeeExpectedDeputyCostsOnSummary()
    {
        $this->iAmOnDeputyCostsSummaryPage();

        $this->expectedResultsDisplayedSimplified(null, true);
    }

    /**
     * @When I visit and start the deputy costs report section for an existing client
     */
    public function visitAndStartDeputyCosts()
    {
        $this->iVisitDeputyCostsSection();
        $this->clickLink('Start');
    }

    /**
     * @When I have assessed deputy costs to declare
     */
    public function iHaveAssessedDeputyCosts()
    {
        $this->chooseOption(
            'deputy_costs[profDeputyCostsHowCharged]',
            'assessed',
            'TypeOfCosts',
            'Assessed costs'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I do not have interim deputy costs to declare
     */
    public function iDoNotHaveInterimDeputyCosts()
    {
        $this->iAmOnDeputyCostsInterimExistsPage();

        $this->chooseOption(
            'yes_no[profDeputyCostsHasInterim]',
            'no',
            'HaveInterimCosts'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I enter a valid amount and description that I am submitting to SCCO for assessment
     */
    public function iEnterValidSCCOAssessmentAmountAndDescription()
    {
        $this->iAmOnDeputyCostsAmountSccoPage();

        $this->fillInField(
            'deputy_costs_scco[profDeputyCostsAmountToScco]',
            1250.44,
            'SCCOAssessment'
        );

        $this->fillInField(
            'deputy_costs_scco[profDeputyCostsReasonBeyondEstimate]',
            $this->faker->sentence(16),
            'SCCOAssessment'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I have charged in line with interim billing under Practice Direction 19B
     */
    public function iHaveChargedInterimCostsInlineWith19B()
    {
        $this->iAmOnDeputyCostsInterimExistsPage();

        $this->chooseOption(
            'yes_no[profDeputyCostsHasInterim]',
            'yes',
            'HaveInterimCosts'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I have not charged in line with interim billing under Practice Direction 19B
     */
    public function iHaveNotChargedInterimCostsInlineWith19B()
    {
        $this->iAmOnDeputyCostsInterimExistsPage();

        $this->chooseOption(
            'yes_no[profDeputyCostsHasInterim]',
            'no',
            'HaveInterimCosts'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I have provided valid interim costs and dates for all three periods
     */
    public function iProvideValidInterimCosts()
    {
        $this->iAmOnDeputyCostsInterimPage();

        $this->fillInFieldTrackTotal(
            'costs_interims[profDeputyInterimCosts][0][amount]',
            5411,
            'CurrentPeriodInterimCosts0'
        );

        $this->fillInDateFields(
            'costs_interims[profDeputyInterimCosts][0][date]',
            $this->faker->numberBetween(1, 27),
            $this->faker->numberBetween(1, 3),
            2020,
            'CurrentPeriodInterimCosts0'
        );

        $this->fillInFieldTrackTotal(
            'costs_interims[profDeputyInterimCosts][1][amount]',
            74,
            'CurrentPeriodInterimCosts1'
        );

        $this->fillInDateFields(
            'costs_interims[profDeputyInterimCosts][1][date]',
            $this->faker->numberBetween(1, 27),
            $this->faker->numberBetween(4, 8),
            2020,
            'CurrentPeriodInterimCosts1'
        );

        $this->fillInFieldTrackTotal(
            'costs_interims[profDeputyInterimCosts][2][amount]',
            945,
            'CurrentPeriodInterimCosts2'
        );

        $this->fillInDateFields(
            'costs_interims[profDeputyInterimCosts][2][date]',
            $this->faker->numberBetween(1, 27),
            $this->faker->numberBetween(9, 12),
            2020,
            'CurrentPeriodInterimCosts2'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I have fixed and assessed deputy costs to declare
     */
    public function iHaveFixedAndAssessedDeputyCosts()
    {
        $this->iAmOnDeputyCostsHowChargedPage();

        $this->chooseOption(
            'deputy_costs[profDeputyCostsHowCharged]',
            'both',
            'TypeOfCosts',
            'Both fixed and assessed costs'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I declare :numberOfCosts previous cost(s) with valid dates and amounts
     */
    public function iDeclarePreviousCostsAndDates(int $numberOfCosts)
    {
        $this->iAmOnDeputyCostsPreviousReceivedPage();

        $year = 2017;

        foreach (range(1, $numberOfCosts) as $index) {
            $this->fillInPreviousReceivedFields($year);
            $allCostsAdded = $index === $numberOfCosts;

            $buttonText = $allCostsAdded ? 'Save and continue' : 'Save and add another';
            $this->pressButton($buttonText);

            if (!$allCostsAdded) {
                $this->assertOnAlertMessage('Cost added');
                ++$year;
            }
        }
    }

    private function fillInPreviousReceivedFields(int $year)
    {
        $this->fillInDateFields(
            'deputy_costs_previous[startDate]',
            $this->faker->numberBetween(1, 27),
            $this->faker->numberBetween(1, 5),
            $year,
            'PreviousReceived'
        );

        $this->fillInDateFields(
            'deputy_costs_previous[endDate]',
            $this->faker->numberBetween(1, 27),
            $this->faker->numberBetween(6, 12),
            $year,
            'PreviousReceived'
        );

        $this->fillInFieldTrackTotal(
            'deputy_costs_previous[amount]',
            1000,
            'PreviousReceived'
        );
    }

    /**
     * @When I have additional costs in all seven categories to declare for the current reporting period
     */
    public function iHaveAllAdditionalCostsToDeclare()
    {
        $this->iAmOnDeputyCostsBreakdownPage();

        foreach (range(0, 6) as $index) {
            $this->fillInFieldTrackTotal(
                "deputy_other_costs[profDeputyOtherCosts][$index][amount]",
                22,
                "AdditionalCosts$index"
            );
        }

        $this->fillInField(
            'deputy_other_costs[profDeputyOtherCosts][6][moreDetails]',
            $this->faker->sentence(20),
            'AdditionalCosts6'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I provide all required information for fixed costs with previous period and additional costs
     */
    public function iProvideAllRequiredInfoForFixedCosts()
    {
        $this->iHaveFixedDeputyCosts();
        $this->clientHasPaidPreviousCostsInCurrentPeriod();
        $this->iDeclarePreviousCostsAndDates(2);
        $this->iEnterValidCurrentCosts();
        $this->iHaveAllAdditionalCostsToDeclare();
    }

    /**
     * @When I provide all required information for assessed costs without previous period and additional costs
     */
    public function iProvideAllRequiredInfoForAssessedCosts()
    {
        $this->iHaveAssessedDeputyCosts();
        $this->clientHasNotPaidPreviousCostsInCurrentPeriod();
        $this->iHaveChargedInterimCostsInlineWith19B();
        $this->iProvideValidInterimCosts();
        $this->iEnterValidSCCOAssessmentAmountAndDescription();
        $this->iHaveNoAdditionalCosts();
    }

    /**
     * @When I edit the details of a cost incurred in a previous period
     */
    public function iEditTheDetailsOfPreviousPeriodCost()
    {
        $locator = '//dt[contains(., "Received for")]/..';
        $previousPeriodCostRow = $this->getSession()->getPage()->find('xpath', $locator);

        $this->editFieldAnswerInSectionTrackTotal(
            $previousPeriodCostRow,
            'deputy_costs_previous[amount]',
            'PreviousReceived'
        );

        $this->iAmOnDeputyCostsSummaryPage();
    }

    /**
     * @When I edit the amount of costs incurred in the current period
     */
    public function iEditTheDetailsOfCurrentPeriodCost()
    {
        $locator = '//dt[contains(., "Paid for this reporting period")]/..';
        $currentPeriodCostRow = $this->getSession()->getPage()->find('xpath', $locator);

        $this->editFieldAnswerInSectionTrackTotal(
            $currentPeriodCostRow,
            'deputy_costs_received[profDeputyFixedCost]',
            'CurrentPeriodFixedCosts'
        );

        $this->iAmOnDeputyCostsSummaryPage();
    }

    /**
     * @When I edit the amount of an additional cost incurred in the current period
     */
    public function iEditTheDetailsOfAdditionalCostCurrentPeriod()
    {
        $locator = '//dt[contains(., "Appointment")]/..';
        $additionalCostRow = $this->getSession()->getPage()->find('xpath', $locator);

        $this->editFieldAnswerInSectionTrackTotal(
            $additionalCostRow,
            'deputy_other_costs[profDeputyOtherCosts][0][amount]',
            'AdditionalCosts0'
        );

        $this->iAmOnDeputyCostsSummaryPage();
    }

    /**
     * @When I change the type of costs incurred to :typeOfCost costs
     */
    public function iChangeTypeOfCostsIncurredToAssessed(string $typeOfCost)
    {
        $locator = '//dt[contains(., "How did you charge for the services")]/..';
        $additionalCostRow = $this->getSession()->getPage()->find('xpath', $locator);

        $this->editSelectAnswerInSection(
            $additionalCostRow,
            'deputy_costs[profDeputyCostsHowCharged]',
            strtolower($typeOfCost),
            'TypeOfCosts',
            'Assessed costs'
        );

        $this->iAmOnDeputyCostsSummaryPage();
    }

    /**
     * @When there should be :numberOfQuestions new question(s) to answer
     */
    public function thereShouldBeTwoNewQuestionsToAnswer(int $numberOfQuestions)
    {
        $locator = '//dd[contains(., "Please answer this question")]/..';
        $additionalCostRow = $this->getSession()->getPage()->findAll('xpath', $locator);

        $this->assertIntEqualsInt(
            $numberOfQuestions,
            count($additionalCostRow),
            'Summary page rows with text "Please answer this question"'
        );
    }

    /**
     * @When I edit the amount of one of the interim interim billing under Practice Direction 19B
     */
    public function iEditOne19BInterimCost()
    {
        $locator = '//dt[contains(., "Costs for interim 1")]/..';
        $interim19BCostsRow = $this->getSession()->getPage()->find('xpath', $locator);

        $this->editFieldAnswerInSectionTrackTotal(
            $interim19BCostsRow,
            'costs_interims[profDeputyInterimCosts][0][amount]',
            'CurrentPeriodInterimCosts0'
        );

        $this->iAmOnDeputyCostsSummaryPage();
    }

    /**
     * @When I edit the amount being submitted to SCCO for assessment
     */
    public function iEditAmountBeingSubmittedToSCCO()
    {
        $locator = '//dt[contains(., "What amount is being submitted to SCCO")]/..';
        $sccoEstimateRow = $this->getSession()->getPage()->find('xpath', $locator);

        $this->editFieldAnswerInSection(
            $sccoEstimateRow,
            'deputy_costs_scco[profDeputyCostsAmountToScco]',
            998,
            'SCCOAssessment'
        );

        $this->iAmOnDeputyCostsSummaryPage();
    }

    /**
     * @When I change my response to charged in line with interim billing under Practice Direction 19B to no
     */
    public function iChangeDirection19BInterimCostsToNo()
    {
        $locator = '//dt[contains(., "Practice Direction 19B")]/..';
        $additionalCostRow = $this->getSession()->getPage()->find('xpath', $locator);

        $this->editSelectAnswerInSection(
            $additionalCostRow,
            'yes_no[profDeputyCostsHasInterim]',
            'no',
            'HaveInterimCosts'
        );

        $this->iAmOnDeputyCostsSummaryPage();
    }

    /**
     * @When I add an additional cost for a previous period from the summary page
     */
    public function iAddPreviousCostFromSummaryPage()
    {
        $this->clickLink('Add another');

        $this->iAmOnDeputyCostsPreviousReceivedPage();

        $this->fillInPreviousReceivedFields(2017);
        $this->pressButton('Save and continue');

        $this->iAmOnDeputyCostsSummaryPage();
    }

    /**
     * @When I remove an additional cost for a previous period from the summary page
     */
    public function iRemovePreviousCostFromSummaryPage()
    {
        $this->removeAnswerFromSection(
            'deputy_costs_previous[amount]',
            'PreviousReceived',
            true,
            'Yes, remove previous cost'
        );

        $this->iAmOnDeputyCostsSummaryPage();
    }

    /**
     * @When I don't provide details of the costs I've incurred
     */
    public function iDontProvideFixedCosts()
    {
        $this->iAmOnDeputyCostsHowChargedPage();
        $this->pressButton('Save and continue');
        $this->iAmOnDeputyCostsHowChargedPage();
    }

    /**
     * @When I don't provide a value for current reporting period fixed costs
     */
    public function iDontProvideFixedCostValue()
    {
        $this->iAmOnDeputyCostsCostsReceievedPage();
        $this->pressButton('Save and continue');
        $this->iAmOnDeputyCostsCostsReceievedPage();
    }

    /**
     * @When I don't choose a response for previous costs
     */
    public function iDontChoosePreviousCostsResponse()
    {
        $this->iAmOnDeputyCostsPreviousReceivedExistsPage();
        $this->pressButton('Save and continue');
        $this->iAmOnDeputyCostsPreviousReceivedExistsPage();
    }

    /**
     * @When I don't provide any details on the previous costs
     */
    public function iProvideNoPreviousCostDetails()
    {
        $this->iAmOnDeputyCostsPreviousReceivedPage();
        $this->pressButton('Save and continue');
        $this->iAmOnDeputyCostsPreviousReceivedPage();
    }

    /**
     * @When I provide an end date that is before the start date
     */
    public function iProvideEndDateBeforeStartDate()
    {
        $this->iAmOnDeputyCostsPreviousReceivedPage();

        $this->fillInDateFields(
            'deputy_costs_previous[startDate]',
            $this->faker->numberBetween(1, 27),
            $this->faker->numberBetween(1, 3),
            2020
        );

        $this->fillInDateFields(
            'deputy_costs_previous[endDate]',
            $this->faker->numberBetween(1, 27),
            $this->faker->numberBetween(1, 3),
            2019
        );

        $this->pressButton('Save and continue');

        $this->iAmOnDeputyCostsPreviousReceivedPage();
    }

    /**
     * @When I provide a negative amount value
     */
    public function iProvideNegativeAmount()
    {
        $this->iAmOnDeputyCostsPreviousReceivedPage();

        $this->fillInField('deputy_costs_previous[amount]', -5);
        $this->pressButton('Save and continue');

        $this->iAmOnDeputyCostsPreviousReceivedPage();
    }

    /**
     * @When I don't provide any interim cost details
     */
    public function iDontProvideAnyInterimCostDetails()
    {
        $this->iAmOnDeputyCostsInterimPage();

        $this->pressButton('Save and continue');

        $this->iAmOnDeputyCostsInterimPage();
    }

    /**
     * @When I provide a valid interim cost amount with a missing date
     */
    public function iProvideAValidAmountAndAMissingDate()
    {
        $this->iAmOnDeputyCostsInterimPage();

        $this->fillInField(
            'costs_interims[profDeputyInterimCosts][0][amount]',
            1075.98,
            'CurrentPeriodInterimCosts0'
        );

        $this->pressButton('Save and continue');

        $this->iAmOnDeputyCostsInterimPage();
    }

    /**
     * @When I provide a valid interim cost date and a missing amount
     */
    public function iProvideAValidDateAndAMissingAmount()
    {
        $this->iAmOnDeputyCostsInterimPage();

        $this->fillInDateFields(
            'costs_interims[profDeputyInterimCosts][0][date]',
            $this->faker->numberBetween(1, 27),
            $this->faker->numberBetween(1, 3),
            2020,
            'CurrentPeriodInterimCosts0'
        );

        $this->fillInField(
            'costs_interims[profDeputyInterimCosts][0][amount]',
            '',
            'CurrentPeriodInterimCosts0'
        );

        $this->pressButton('Save and continue');

        $this->iAmOnDeputyCostsInterimPage();
    }

    /**
     * @When I provide a valid interim cost date and an amount outside the amount limit
     */
    public function iProvideAValidDateAndAnAmountOutsideTheAmountLimit()
    {
        $this->iAmOnDeputyCostsInterimPage();

        $this->fillInDateFields(
            'costs_interims[profDeputyInterimCosts][0][date]',
            $this->faker->numberBetween(1, 27),
            $this->faker->numberBetween(1, 3),
            2020,
        );

        $this->fillInField(
            'costs_interims[profDeputyInterimCosts][0][amount]',
            $this->faker->randomElement([-0.01, 100000000000.1]),
            'CurrentPeriodInterimCosts0'
        );

        $this->pressButton('Save and continue');

        $this->iAmOnDeputyCostsInterimPage();
    }

    /**
     * @When I don't enter an SCCO assessed cost amount
     */
    public function iDontEnterAnSccoAssessedCostAmount()
    {
        $this->iAmOnDeputyCostsAmountSccoPage();

        $this->pressButton('Save and continue');

        $this->iAmOnDeputyCostsAmountSccoPage();
    }

    /**
     * @When I enter a negative SCCO assessed cost amount
     */
    public function iEnterANegativeSccoAssessedCostAmount()
    {
        $this->iAmOnDeputyCostsAmountSccoPage();

        $this->fillInField(
            'deputy_costs_scco[profDeputyCostsAmountToScco]',
            -0.01,
            'SCCOAssessment'
        );

        $this->pressButton('Save and continue');

        $this->iAmOnDeputyCostsAmountSccoPage();
    }

    /**
     * @When I provide 6 negative and 1 too large amounts for all seven additional cost types
     */
    public function iProvideNegativeAndTooLargeAmountsForAllSevenAdditionalCostTypes()
    {
        $this->iAmOnDeputyCostsBreakdownPage();

        foreach (range(0, 5) as $index) {
            $this->fillInField(
                "deputy_other_costs[profDeputyOtherCosts][$index][amount]",
                -500
            );
        }

        $this->fillInField(
            'deputy_other_costs[profDeputyOtherCosts][6][amount]',
            1000000000000.1
        );

        $this->pressButton('Save and continue');

        $this->iAmOnDeputyCostsBreakdownPage();
    }

    /**
     * @Then I provide a valid 'Other' cost but no description
     */
    public function iProvideAValidCostButNoDescription()
    {
        $this->iAmOnDeputyCostsBreakdownPage();

        $this->fillInField(
            'deputy_other_costs[profDeputyOtherCosts][6][amount]',
            22.98
        );

        $this->pressButton('Save and continue');

        $this->iAmOnDeputyCostsBreakdownPage();
    }

    /**
     * @Then I should see a(n) :errorType deputy costs error
     * @Then I should see :errorType deputy costs errors
     */
    public function iShouldSeeADeputyCostsError(string $errorType)
    {
        switch (strtolower($errorType)) {
            case 'amount limit':
            case 'amount outside of limit':
                $this->assertOnErrorMessage($this->amountRangeLimitError);
                break;
            case 'missing cost type':
                $this->assertOnErrorMessage($this->missingCostTypeError);
                break;
            case 'please choose yes or no':
                $this->assertOnErrorMessage($this->missingPreviousCostsIncurredError);
                break;
            case 'empty dates and value':
                $this->assertOnErrorMessage($this->emptyPreviousCostStartDateError);
                $this->assertOnErrorMessage($this->emptyPreviousCostEndDateError);
                $this->assertOnErrorMessage($this->missingValueError);
                break;
            case 'end date before start date':
                $this->assertOnErrorMessage($this->endDateBeforeStartDateError);
                break;
            case 'at least one cost required':
                $this->assertOnErrorMessage($this->missingInterimCostError);
                break;
            case 'date required':
                $this->assertOnErrorMessage($this->missingDateError);
                break;
            case 'missing fixed cost amount':
                $this->assertOnErrorMessage($this->missingFixedCostAmountError);
                break;
            case 'amount required':
                $this->assertOnErrorMessage($this->missingValueError);
                break;
            case 'missing scco assesssed cost amount':
                $this->assertOnErrorMessage($this->missingSccoAssessedCostError);
                break;
            case 'negative scco assessed cost amount':
                $this->assertOnErrorMessage($this->negativeSccoAssessedCostError);
                break;
            case '6 negative and 1 too large amounts':
                foreach (range(0, 5) as $index) {
                    $this->assertOnErrorMessage($this->additionalCostNegativeAmountError);
                }

                $this->assertOnErrorMessage($this->additionalCostTooLargeAmountError);
                break;
            case 'missing other cost description':
                $this->assertOnErrorMessage($this->additionalCostMissingOtherDescriptionError);
                break;
            default:
                throw new BehatException(sprintf('Error type "%s" not recognised. See DeputyCostsSectionTrait::iShouldSeeAError() for types', $errorType));
        }
    }
}
