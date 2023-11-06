<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use Behat\Gherkin\Node\TableNode;

trait MoneyInLowAssetsTrait
{
    private int $moneyOutPaymentCount = 0;
    private array $moneyInShortTypeDictionary = [
        0 => 'State pension and benefits',
        1 => 'Bequests - for example, inheritance, gifts received',
        2 => 'Income from investments, dividends, property rental',
        3 => 'Sale of investments, property or assets',
        4 => 'Salary or wages',
        5 => 'Compensations and damages awards',
        6 => 'Personal pension',
    ];

    private array $moneyInShortList = [];
    private array $moneyInShortOneOff = [];
    private float $moneyInShortTotal = 0.0;

    /**
     * @When I view and start the money in short report section
     */
    public function iViewAndStartMoneyInShortSection()
    {
        $this->iVisitMoneyInShortSection();
        $this->pressButton('Start money in');
    }

    /**
     * @Given I have no payments going out
     */
    public function iHaveNoPaymentsGoingOut()
    {
        $this->iAmOnMoneyInShortCategoryPage();
        $this->pressButton('Save and continue');
        $this->iHaveNoOneOffPayments();
    }

    /**
     * @Given I have no one-off payments over £1k
     */
    public function iHaveNoOneOffPayments()
    {
        $this->chooseOption(
            'yes_no[moneyTransactionsShortInExist]',
            'no',
            'one-off-payments'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I select no categories for money in
     */
    public function iSelectNoCategoriesForMoneyIn()
    {
        $this->pressButton('Save and continue');
    }

    /**
     * @When I don't select a one off payment option
     */
    public function iDontSelectOneOffPayment()
    {
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see a select option validation error
     */
    public function iShouldSeeSelectOptionValidationError()
    {
        $this->assertOnErrorMessage("Please select either 'Yes' or 'No'");
    }

    /**
     * @Given I am reporting on:
     */
    public function iAmReportingOnMoneyInType(TableNode $moneyInTypes)
    {
        foreach ($moneyInTypes as $moneyInType) {
            $optionIndex = array_search($moneyInType['Benefit Type'], $this->moneyInShortTypeDictionary);

            $this->tickCheckbox(
                'money-types',
                $this->moneyInShortTypeDictionary[$optionIndex],
//                "money_short[moneyShortCategoriesIn][$optionIndex][present]",
                'money-types',
                $this->moneyInShortTypeDictionary[$optionIndex]
            );
        }

        $this->pressButton('Save and continue');
    }

    /**
     * @Given I don't have a one off payment
     */
    public function iDontHaveAOneOffPayment()
    {
        $this->chooseOption(
            'yes_no[moneyTransactionsShortInExist]',
            'no',
            'one-off-payments'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @Given I have a single one-off payments over £1k
     */
    public function iHaveASingleOneOffPaymentOver1k()
    {
        $this->chooseOption(
            'yes_no[moneyTransactionsShortInExist]',
            'yes',
            'one-off-payments'
        );

        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'yes_no_save');

        $this->addMoneyOutPayment('Lorem ipsum', 1500);

        $this->chooseOption('add_another[addAnother]', 'no');
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'add_another_save');
    }

    /**
     * @When I edit an existing money in short payment
     */
    public function iEditOneOffMoneyInPayment()
    {
        $this->iVisitMoneyInShortSummarySection();
        $this->iAmOnMoneyInShortSummaryPage();
        $urlRegex = sprintf('/%s\/.*\/money-in-short\/oneOffPaymentsExist\?from\=summary$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);

        $this->chooseOption('yes_no[moneyTransactionsShortInExist]', 'yes', 'one-off-payments');
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'yes_no_save');

        $this->addMoneyOutPayment('Lorem ipsum', 1500, '08/12/2021');

        $this->chooseOption('add_another[addAnother]', 'no');
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'add_another_save');

        $this->iAmOnMoneyInShortSummaryPage();
    }

    /**
     * @When I add a one off money in payment that is less than £1k
     */
    public function iAddAOneOffMoneyInPaymentThatIsLessThan1k()
    {
        $this->iVisitMoneyInShortSummarySection();
        $this->iAmOnMoneyInShortSummaryPage();
        $urlRegex = sprintf('/%s\/.*\/money-in-short\/oneOffPaymentsExist\?from\=summary$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);

        $this->chooseOption('yes_no[moneyTransactionsShortInExist]', 'yes', 'one-off-payments');
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'yes_no_save');

        $this->addMoneyOutPayment('Lorem upsum', 10, '05/05/2015');
    }

    /**
     * @Then I should the see correct validation message
     */
    public function iShouldSeeTheCorrectValidationMessage()
    {
        $this->assertOnAlertMessage('Please input a value of at least £1,000');
    }

    /**
     * @param string      $description description of the pne off payment
     * @param int         $amount      amount for the one off payment
     * @param string|null $date        date the money came in (optional) format: DD/MM/YYYY
     */
    private function addMoneyOutPayment(string $description, int $amount, ?string $date = null)
    {
        ++$this->moneyOutPaymentCount;

        $this->iAmOnMoneyInShortAddPage();

        $this->fillInField('money_short_transaction[description]', $description, 'payment'.$this->moneyOutPaymentCount);
        $this->fillInFieldTrackTotal('money_short_transaction[amount]', $amount, 'payment'.$this->moneyOutPaymentCount);

        if (null !== $date) {
            $explodedDate = explode('/', $date);

            $this->fillInDateFields(
                'money_short_transaction[date]',
                intval($explodedDate[0]),
                intval($explodedDate[1]),
                intval($explodedDate[2]),
                'payment'.$this->moneyOutPaymentCount
            );
        }

        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'money_short_transaction_save');
    }

    /**
     * @Then I should see the expected money in section summary
     */
    public function iShouldSeeTheExpectedMoneyInSectionSummary()
    {
        $this->iAmOnMoneyInShortSummaryPage();

        $this->expectedResultsDisplayedSimplified();
    }
}
