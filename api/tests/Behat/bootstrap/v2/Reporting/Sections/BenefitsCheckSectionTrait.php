<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait BenefitsCheckSectionTrait
{
    /**
     * @When I navigate to the client benefits check report section
     */
    public function iNavigateToBenefitsCheckSection()
    {
    }

    /**
     * @When I navigate to and start the client benefits check report section
     */
    public function iNavigateToAndStartBenefitsCheckSection()
    {
    }

    /**
     * @When I confirm I checked the clients benefit entitlement on :dateString
     */
    public function iConfirmCheckedBenefitsOnDate(string $dateString)
    {
    }

    /**
     * @When I confirm I am currently checking the benefits the client is entitled to
     */
    public function iConfirmCurrentlyCheckingBenefits()
    {
    }

    /**
     * @When I confirm I have never checked the benefits the client is entitled to
     */
    public function iConfirmHaveNeverCheckedBenefits()
    {
    }

    /**
     * @When I confirm others receive income on the clients behalf
     */
    public function iConfirmOthersReceiveIncomeOnClientsBehalf()
    {
    }

    /**
     * @When I confirm others do not receive income on the clients behalf
     */
    public function iConfirmOthersDoNotReceiveIncomeOnClientsBehalf()
    {
    }

    /**
     * @When I add :numOfIncomeTypes type(s) of income
     */
    public function iAddNumberOfIncomeTypes(int $numOfIncomeTypes)
    {
    }

    /**
     * @When I add an income type from the summary page
     */
    public function iAddIncomeTypeFromSummaryPage(int $numOfIncomeTypes)
    {
    }

    /**
     * @When I :action the last type of income I added
     */
    public function iActionIncomeTypeIAdded(string $action)
    {
    }

    /**
     * @Then the client benefits check summary page should contain the details I entered
     */
    public function benefitCheckSummaryPageContainsEnteredDetails()
    {
    }
}
