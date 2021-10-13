<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use App\Entity\Report\Report;
use App\Tests\Behat\BehatException;

trait ClientBenefitsCheckSectionTrait
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
        $this->iVisitReportOverviewPage();
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

        $this->chooseOption('report-client-benefits-check[whenLastCheckedEntitlement]', 'haveChecked');

        $this->fillInDateFields(
            'report-client-benefits-check[dateLastCheckedEntitlement]',
            null,
            intval($explodedDate[1]),
            intval($explodedDate[2]),
            'haveCheckedBenefits'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I confirm I am currently checking the benefits the client is entitled to
     */
    public function iConfirmCurrentlyCheckingBenefits()
    {
        $this->chooseOption(
            'report-client-benefits-check[whenLastCheckedEntitlement]',
            'currentlyChecking',
            'haveCheckedBenefits',
            'I\'m currently checking this'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I confirm I have never checked the benefits the client is entitled to and provide a reason
     */
    public function iConfirmHaveNeverCheckedBenefits()
    {
        $this->chooseOption(
            'report-client-benefits-check[whenLastCheckedEntitlement]',
            'neverChecked',
            'haveCheckedBenefits',
            'I\'ve never checked this'
        );

        $this->fillInField(
            'report-client-benefits-check[neverCheckedExplanation]',
            $this->faker->sentence(280),
            'haveCheckedBenefits'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I confirm others receive income on the clients behalf
     */
    public function iConfirmOthersReceiveIncomeOnClientsBehalf()
    {
        $this->chooseOption(
            'report-client-benefits-check[doOthersReceiveIncomeOnClientsBehalf]',
            'yes'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I confirm others do not receive income on the clients behalf
     */
    public function iConfirmOthersDoNotReceiveIncomeOnClientsBehalf()
    {
        $this->chooseOption(
            'report-client-benefits-check[doOthersReceiveIncomeOnClientsBehalf]',
            'no'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I add :numOfIncomeTypes type(s) of income with values
     */
    public function iAddNumberOfIncomeTypes(int $numOfIncomeTypes)
    {
        $numOfIncomeTypes = $numOfIncomeTypes - 1;

        foreach (range(0, $numOfIncomeTypes) as $index) {
            $this->fillInField(
                "report-client-benefits-check[typesOfIncomeReceivedOnClientsBehalf][$index][incomeType]",
                $this->faker->sentence(3),
                'incomeType'
            );

            $this->fillInField(
                "report-client-benefits-check[typesOfIncomeReceivedOnClientsBehalf][$index][amount]",
                $this->faker->numberBetween(10, 2000),
                'incomeType'
            );

            $this->pressButton('Add another');
        }
    }

    /**
     * @When I add a type of income where I don't know the value
     */
    public function iAddIncomeTypeWithNoValue()
    {
        // Add the forms into a div or form group in template then find the last element and fill in below.

        $incomeTypesXpath = "//fieldset[contains(@class, 'add-another__item')]";
        $incomeTypes = $this->getSession()->getPage()->findAll('xpath', $incomeTypesXpath);

        $emptyIncomeType = null;

        foreach ($incomeTypes as $incomeType) {
            $emptyInputValueGrandparentXpath = '//input[not(@value)]/../..';
            $emptyIncomeType = $incomeType->find('xpath', $emptyInputValueGrandparentXpath) ?: null;
        }

        $incomeTypeByNameXpath = "//input[contains(@name, 'incomeType')]";
        $incomeTypeName = ($emptyIncomeType->find('xpath', $incomeTypeByNameXpath))->getAttribute('name');

        $this->fillInField($incomeTypeName, $this->faker->sentence(2), 'incomeType');

        $checkboxByNameXpath = "//input[contains(@type, 'checkbox')]";
        $checkboxName = ($emptyIncomeType->find('xpath', $checkboxByNameXpath))->getAttribute('name');

        $this->tickCheckbox(
            'incomeTypeCheckbox',
            $checkboxName,
            'incomeType',
            'I don\'t know'
        );

        $this->pressButton('Add another');
    }

    /**
     * @When I have no further types of income to add
     */
    public function iHaveNoFurtherTypesOfIncomeToAdd()
    {
        $this->pressButton('Save and continue');
    }

    /**
     * @When I add :numOfIncomeTypes income types from the summary page
     */
    public function iAddIncomeTypesFromSummaryPage(int $numOfIncomeTypes)
    {
        $this->iAmOnClientBenefitsCheckSummaryPage();

        $this->pressButton('Add income');

        $this->iAddNumberOfIncomeTypes($numOfIncomeTypes);
        $this->iHaveNoFurtherTypesOfIncomeToAdd();
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
        $this->iAmOnClientBenefitsCheckSummaryPage();

        if (!is_null($this->getSectionAnswers('haveCheckedBenefits'))) {
            $this->expectedResultsDisplayedSimplified('haveCheckedBenefits');
        }

        if (!is_null($this->getSectionAnswers('incomeType'))) {
            $this->expectedResultsDisplayedSimplified('incomeType');
        }
    }

    /**
     * @Given the deputies report ends and is due :moreOrLess than 60 days after the client benefits check feature flag date
     */
    public function reportIsDueAfterClientBenefitCheckFeatureFlagDate(string $moreOrLess)
    {
        $moreOrLess = strtolower($moreOrLess);

        if (!in_array($moreOrLess, ['more', 'less'])) {
            throw new BehatException(sprintf('This step only accepts "more" or "less". %s provided.', $moreOrLess));
        }

        if ('more' === $moreOrLess) {
            $this->endDateAndDueDateLoggedInUsersCurrentReportSetToDate('2040-01-01');
        } else {
            $this->endDateAndDueDateLoggedInUsersCurrentReportSetToDate('2020-01-01');
        }
    }

    /**
     * @Given they have not completed the client benefits section
     */
    public function haveNotCompletedBenefitsSection()
    {
        if (empty($this->loggedInUserDetails) && empty($this->loggedInUserDetails->getCurrentReportId())) {
            throw new Exception('The logged in user does not have a report. Ensure a user with a report has logged in before using this step.');
        }

        /** @var Report $currentReport */
        $currentReport = $this->em->getRepository(Report::class)->find($this->loggedInUserDetails->getCurrentReportId());
        $currentReport->setClientBenefitsCheck(null);

        $this->em->persist($currentReport);
        $this->em->flush();
    }
}
