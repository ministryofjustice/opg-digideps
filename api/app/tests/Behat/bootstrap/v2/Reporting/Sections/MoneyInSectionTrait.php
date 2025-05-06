<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use App\Tests\Behat\BehatException;

trait MoneyInSectionTrait
{
    // Expected validation errors
    private string $invalidSelectOptionError = 'Please choose an option';
    private string $enterAmountError = 'Please enter an amount';
    private string $invalidAmountError = 'The amount must be between £0.01 and £100,000,000,000';

    private string $currentMoneyTypeReportingOn = '';

    private array $moneyTypeDictionary = [
        'Salary or wages' => 'salary-or-wages',
        'Interest on savings and other accounts' => 'account-interest',
        'Dividends' => 'dividends',
        'Income from property rental' => 'income-from-property-rental',
        'Private pension' => 'personal-pension',
        'State pension' => 'state-pension',
        'Attendance Allowance' => 'attendance-allowance',
        'Disability Living Allowance' => 'disability-living-allowance',
        'Employment Support Allowance' => 'employment-support-allowance',
        'Housing Benefit' => 'housing-benefit',
        'Incapacity Benefit' => 'incapacity-benefit',
        'Income Support' => 'income-support',
        'Pension Credit' => 'pension-credit',
        'Severe Disablement Allowance' => 'severe-disablement-allowance',
        'Universal Credit' => 'universal-credit',
        'Winter Fuel/Cold Weather Payment' => 'winter-fuel-cold-weather-payment',
        'Other benefits' => 'other-benefits',
        'Compensation or damages award' => 'compensation-or-damages-award',
        'Bequest or inheritance' => 'bequest-or-inheritance',
        'Cash gift received' => 'cash-gift-received',
        'Refund' => 'refunds',
        'Sale of asset' => 'sale-of-asset',
        'Sale of investment' => 'sale-of-investment',
        'Anything else' => 'anything-else',
    ];

    private array $moneyTypeCategoriesCompleted = [];
    private array $moneyInTransaction = [];

    /**
     * @When I view the money in report section
     */
    public function iViewMoneyInSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'money-in');
        $this->visitPath($reportSectionUrl);
    }

    /**
     * @Given /^I confirm "([^"]*)" to adding money in on the clients behalf$/
     */
    public function iConfirmToAddingMoneyInOnTheClientsBehalf($arg1)
    {
        $this->chooseOption('does_money_in_exist[moneyInExists]', $arg1, 'moneyInExists');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I view and start the money in report section
     */
    public function iViewAndStartMoneyInSection()
    {
        $this->iViewMoneyInSection();
        $this->clickLink('Start money in');
    }

    /**
     * @Then I click save and continue
     */
    public function iClickSaveAndContinue()
    {
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see a select option error
     */
    public function iShouldSeeASelectOptionError()
    {
        $this->assertOnErrorMessage($this->invalidSelectOptionError);
    }

    /**
     * @Given I have :moneyType to report on
     * @Given I have a/an :moneyType to report on
     */
    public function iHaveMoneyTypeToReportOn(string $moneyType)
    {
        $option = $this->translateMoneyType($moneyType);
        $this->chooseOption('account[category]', $option, $moneyType, $moneyType);
        $this->pressButton('Save and continue');
        $this->currentMoneyTypeReportingOn = $moneyType;
    }

    /**
     * @param string $moneyTypeLabel The user facing money type translation e.g. Interest on savings and other accounts
     *                               rather than account-interest
     *
     * @return mixed|string
     *
     * @throws BehatException
     */
    private function translateMoneyType(string $moneyTypeLabel)
    {
        $categories = array_keys($this->moneyTypeDictionary);

        if (!in_array($moneyTypeLabel, $categories)) {
            $validCategories = implode($categories);
            throw new BehatException(sprintf('The money in category label you used doesn\'t exist. Valid categories are: %s', $validCategories));
        }

        return $this->moneyTypeDictionary[$moneyTypeLabel];
    }

    /**
     * @Given I try to submit an empty amount
     */
    public function iTryToSubmitAnEmptyAmount()
    {
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see an empty field error
     */
    public function iShouldSeeAnEmptyFieldError()
    {
        $this->assertOnErrorMessage($this->enterAmountError);
    }

    /**
     * @Given I try to submit an invalid amount
     */
    public function iTryToSubmitAnInvalidAmount()
    {
        $this->fillInField('account[amount]', '0');

        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see an invalid field error
     */
    public function iShouldSeeAnInvalidFieldError()
    {
        $this->assertOnErrorMessage($this->invalidAmountError);
    }

    /**
     * @Given I enter a valid amount
     */
    public function iEnterAValidAmount()
    {
        $value = $this->faker->numberBetween(1, 10000);

        $this->fillInFieldTrackTotal(
            'account[amount]',
            $value,
            $this->currentMoneyTypeReportingOn
        );

        $this->moneyInTransaction[] = [$this->currentMoneyTypeReportingOn => $value];

        $this->pressButton('Save and continue');
    }

    /**
     * @Given I dont add another item
     */
    public function iDontAddAnotherItem()
    {
        $this->chooseOption('add_another[addAnother]', 'no');
        $this->pressButton('Save and continue');
        $this->moneyTypeCategoriesCompleted[] = $this->currentMoneyTypeReportingOn;
    }

    /**
     * @Then I should see the money in summary
     */
    public function iShouldSeeTheMoneyInSummary(): bool
    {
        return $this->iAmOnPage('/report\/.*\/money-in\/summary.*$/');
    }

    /**
     * @When I add another item
     */
    public function iAddAnotherItem()
    {
        $this->chooseOption('add_another[addAnother]', 'yes');
        $this->pressButton('Save and continue');
        $this->moneyTypeCategoriesCompleted[] = $this->currentMoneyTypeReportingOn;
    }

    /**
     * @Then /^the money in summary page should contain "([^"]*)" money in values$/
     */
    public function theMoneyInSummaryPageShouldContainMoneyInValues($arg1)
    {
        assert($this->iShouldSeeTheMoneyInSummary());

        $transactionItemTableRows = $this->getSession()->getPage()->find('xpath', "//tr[contains(@class,'behat-region-transaction-')]");

        if ('no' == $arg1) {
            $this->assertIsNull($transactionItemTableRows, 'transaction item rows are not rendered');

            $this->expectedResultsDisplayedSimplified(null, true, false, false, false);
        } else {
            $this->assertPageContainsText('Income you\'ve already told us about');

            foreach ($this->moneyInTransaction as $transactionItems) {
                foreach ($transactionItems as $moneyType => $value) {
                    $this->assertElementContainsText('main', $moneyType);
                    $this->assertElementContainsText('main', '£'.number_format($value, 2));
                }
            }
            $this->expectedResultsDisplayedSimplified();
        }
    }

    /**
     * @Given I remove the dividends item
     */
    public function iRemoveTheDividendsItem()
    {
        $this->clickLink('Remove');
        assert($this->iShouldBeOnTheDeletePage());
        $this->pressButton('Yes, remove item of income');
    }

    /**
     * @Then I should be on the delete page
     */
    public function iShouldBeOnTheDeletePage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/money-in\/.*\/delete$/');
    }

    /**
     * @Then I should be on the money in summary page and see entry deleted
     */
    public function iShouldBeOnTheMoneyInSummaryPageAndSeeEntryDeleted()
    {
        $entryDeletedText = $this->getSession()->getPage()->find('css', '.opg-alert__message > .govuk-body')->getText();
        assert('Entry deleted' == $entryDeletedText);
    }

    /**
     * @When I edit the money in value
     */
    public function iEditTheMoneyInValue()
    {
        $xpath = sprintf('//tr[td[text()[contains(.,"%s")]]]', $this->currentMoneyTypeReportingOn);
        $moneyTypeRow = $this->getSession()->getPage()->find(
            'xpath',
            $xpath
        );

        $newValue = $this->faker->numberBetween(1, 10000);

        $this->editFieldAnswerInSectionTrackTotal(
            $moneyTypeRow,
            'account[amount]',
            $this->currentMoneyTypeReportingOn,
            false,
            $newValue
        );

        foreach ($this->moneyInTransaction[0] as $moneyType => $value) {
            $this->subtractFromSectionTotal($this->currentMoneyTypeReportingOn, $value);
            $this->moneyInTransaction[0][$this->currentMoneyTypeReportingOn] = $newValue;
        }
    }

    /**
     * @Then the money in summary page should contain the edited value
     */
    public function theMoneyInSummaryPageShouldContainTheEditedValue()
    {
        $this->theMoneyInSummaryPageShouldContainMoneyInValues('1');
    }

    /**
     * @When I add another single item of income
     */
    public function iAddAnotherSingleItemOfIncome()
    {
        $this->clickLink('Add item of income');

        $moneyTypeLabel = 'State pension';
        $option = $this->translateMoneyType($moneyTypeLabel);

        $this->chooseOption('account[category]', $option, $moneyTypeLabel, $moneyTypeLabel);
        $this->pressButton('Save and continue');
        $this->currentMoneyTypeReportingOn = $moneyTypeLabel;

        $this->iEnterAValidAmount();
    }

    /**
     * @Then /^I enter a reason for no money in$/
     */
    public function iEnterAReasonForNoMoneyIn()
    {
        $this->iAmOnNoMoneyInExistsPage();

        $this->fillInField('reason_for_no_money[reasonForNoMoneyIn]', 'No money in', 'reasonForNoMoneyIn');
        $this->pressButton('Save and continue');
    }

    /**
     * @When /^I edit the money in exist summary section$/
     */
    public function iEditTheMoneyInExistSummarySection()
    {
        $this->iShouldSeeTheMoneyInSummary();

        // clean data to correctly track expected results when user edits answers.
        $this->removeSection('moneyInExists');
        $this->removeSection($this->currentMoneyTypeReportingOn);
        $this->removeSection('reasonForNoMoneyIn');

        $urlRegex = sprintf('/%s\/.*\/money-in\/exist\?from\=summary$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
    }

    /**
     * @When /^I delete the transaction item from the summary page$/
     */
    public function iDeleteTheTransactionItemFromTheSummaryPage()
    {
        $this->iShouldSeeTheMoneyInSummary();

        $this->removeAnswerFromSection(
            'account[category]',
            $this->currentMoneyTypeReportingOn,
            true,
            'Yes, remove item of income'
        );

        foreach ($this->moneyInTransaction[0] as $moneyType => $value) {
            $this->subtractFromGrandTotal($value);
        }

        $this->moneyInTransaction = [];
    }

    /**
     * @Given /^I add a new transaction item$/
     */
    public function iAddANewTransactionItem()
    {
        $this->clickLink('Add item of income');
        $this->iHaveMoneyTypeToReportOn('Income Support');
        $this->fillField('account[amount]', '200');

        $this->pressButton('Save and continue');
    }
}
