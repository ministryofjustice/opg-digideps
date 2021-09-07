<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use App\Tests\Behat\BehatException;

trait BenefitsCheckSectionTrait
{
    /**
     * @When I navigate to the client benefits check report section
     */
    public function iNavigateToBenefitsCheckSection()
    {
        $this->clickLink('Benefits check and income other people receive');
    }

    /**
     * @When I navigate to and start the client benefits check report section
     */
    public function iNavigateToAndStartBenefitsCheckSection()
    {
        $this->iNavigateToBenefitsCheckSection();
        // May be a button
        $this->clickLink('Start');
    }

    /**
     * @When I confirm I checked the clients benefit entitlement on :dateString
     */
    public function iConfirmCheckedBenefitsOnDate(string $dateString)
    {
        $explodedDate = explode('/', $dateString);

        $this->chooseOption('addSelectName', 'haveCheckedBenefits', 'add translation');

        $this->fillInDateFields(
            'addFieldName',
            null,
            intval($explodedDate[0]),
            intval($explodedDate[1]),
            'haveCheckedBenefits'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I confirm I am currently checking the benefits the client is entitled to
     */
    public function iConfirmCurrentlyCheckingBenefits()
    {
        $this->chooseOption('addSelectName', 'addOption', 'haveCheckedBenefits', 'add translation');

        $this->pressButton('Save and continue');
    }

    /**
     * @When I confirm I have never checked the benefits the client is entitled to
     */
    public function iConfirmHaveNeverCheckedBenefits()
    {
        $this->chooseOption('addSelectName', 'addOption', 'haveCheckedBenefits', 'add translation');

        $this->pressButton('Save and continue');
    }

    /**
     * @When I confirm others receive income on the clients behalf
     */
    public function iConfirmOthersReceiveIncomeOnClientsBehalf()
    {
        $this->chooseOption('addSelectName', 'addOption', 'haveOthersReceivedIncome', 'add translation');

        $this->pressButton('Save and continue');
    }

    /**
     * @When I confirm others do not receive income on the clients behalf
     */
    public function iConfirmOthersDoNotReceiveIncomeOnClientsBehalf()
    {
        $this->chooseOption('addSelectName', 'addOption', 'haveOthersReceivedIncome', 'add translation');

        $this->pressButton('Save and continue');
    }

    /**
     * @When I add :numOfIncomeTypes type(s) of income with values
     */
    public function iAddNumberOfIncomeTypes(int $numOfIncomeTypes)
    {
        foreach (range(0, $numOfIncomeTypes) as $index) {
            $this->fillInField('addFieldName', $this->faker->words(2), 'incomeType');
            $this->fillInFieldTrackTotal('addFieldName', $this->faker->numberBetween(10, 2000), 'incomeType');

            if ($index === $numOfIncomeTypes) {
                break;
            }

            $this->pressButton('Add another');
        }

        $this->pressButton('Save and continue');
    }

    /**
     * @When I add a type of income where I don't know the value
     */
    public function iAddIncomeTypeWithNoValue()
    {
        $this->fillInField('addFieldName', $this->faker->words(2), 'incomeType');
        $this->tickCheckbox(
            'addGroupName',
            'addOptionName',
            'incomeType',
            'I don\'t know the amount'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I add an income type from the summary page
     */
    public function iAddIncomeTypeFromSummaryPage(int $numOfIncomeTypes)
    {
        $this->iAmOnDeputyBenefitsCheckSummaryPage();

        $this->pressButton('Add income');

        $this->fillInField('addFieldName', $this->faker->words(2), 'incomeType');
        $this->fillInFieldTrackTotal('addFieldName', $this->faker->numberBetween(10, 2000), 'incomeType');

        $this->pressButton('Save and continue');
    }

    /**
     * @When I :action the last type of income I added
     */
    public function iActionIncomeTypeIAdded(string $action)
    {
        if ('edit' === strtolower($action)) {
        } elseif ('remove' === strtolower($action)) {
        } else {
            throw new BehatException('This step definition only supports "edit" and "remove"');
        }
    }

    /**
     * @Then the client benefits check summary page should contain the details I entered
     */
    public function benefitCheckSummaryPageContainsEnteredDetails()
    {
        $this->expectedResultsDisplayedSimplified();
    }
}
