<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use App\Tests\Behat\BehatException;

trait DeputyCostsSectionTrait
{
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

        $this->clickLink($clientName);
        $this->clickLink('Deputy costs');
        $this->clickLink('Start');
    }

    /**
     * @When I have fixed deputy costs to declare
     */
    public function iHaveFixedDeputyCosts()
    {
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
        $this->chooseOption(
            'yes_no[profDeputyCostsHasPrevious]',
            'no',
            'HasPreviousCosts'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I enter a valid amount for the current reporting period costs
     */
    public function iEnterValidCurrentCosts()
    {
        $this->fillInFieldTrackTotal(
            'deputy_costs_received[profDeputyFixedCost]',
            $this->faker->numberBetween(10, 10000),
            'CurrentPeriodFixedCosts'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I have no additional costs to declare for the current reporting period
     */
    public function iHaveNoAdditionalCosts()
    {
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see the expected responses on the deputy costs summary page
     */
    public function iShouldSeeExpectedDeputyCostsOnSummary()
    {
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
        $this->fillInField(
            'deputy_costs_scco[profDeputyCostsAmountToScco]',
            $this->faker->numberBetween(10, 10000),
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
        $this->chooseOption(
            'yes_no[profDeputyCostsHasInterim]',
            'yes',
            'HaveInterimCosts'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I have provided valid interim costs and dates for all three periods
     */
    public function iProvideValidInterimCosts()
    {
        $this->fillInFieldTrackTotal(
            'costs_interims[profDeputyInterimCosts][0][amount]',
            $this->faker->numberBetween(10, 10000),
            'CurrentPeriodInterimCosts'
        );

        $this->fillInDateFields(
            'costs_interims[profDeputyInterimCosts][0][date]',
            $this->faker->numberBetween(1, 27),
            $this->faker->numberBetween(1, 3),
            2020,
            'CurrentPeriodInterimCosts'
        );

        $this->fillInFieldTrackTotal(
            'costs_interims[profDeputyInterimCosts][1][amount]',
            $this->faker->numberBetween(10, 10000),
            'CurrentPeriodInterimCosts'
        );

        $this->fillInDateFields(
            'costs_interims[profDeputyInterimCosts][1][date]',
            $this->faker->numberBetween(1, 27),
            $this->faker->numberBetween(4, 8),
            2020,
            'CurrentPeriodInterimCosts'
        );

        $this->fillInFieldTrackTotal(
            'costs_interims[profDeputyInterimCosts][2][amount]',
            $this->faker->numberBetween(10, 10000),
            'CurrentPeriodInterimCosts'
        );

        $this->fillInDateFields(
            'costs_interims[profDeputyInterimCosts][2][date]',
            $this->faker->numberBetween(1, 27),
            $this->faker->numberBetween(9, 12),
            2020,
            'CurrentPeriodInterimCosts'
        );

        $this->pressButton('Save and continue');
    }
}
