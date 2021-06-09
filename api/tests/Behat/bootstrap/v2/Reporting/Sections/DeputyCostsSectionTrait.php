<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use App\Tests\Behat\BehatException;

trait DeputyCostsSectionTrait
{
    private array $completedSections = [];

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

        $this->completedSections[] = 'TypeOfCosts';

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
        $this->expectedResultsDisplayedSimplified();
    }
}
