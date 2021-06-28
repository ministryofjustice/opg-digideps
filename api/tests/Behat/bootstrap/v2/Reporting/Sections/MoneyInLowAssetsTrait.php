<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait MoneyInLowAssetsTrait
{
    private array $moneyInShortTypeDictionary = [
        'State pension and benefits',
        'Bequests – for example, inheritance, gifts received',
        'Income from investments, dividends, property rental',
        'Sale of investments, property or assets',
        'Salary or wages',
        'Compensations and damages awards',
        'Personal pension',
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
        $this->selectOption('yes_no[moneyTransactionsShortInExist]', 'no');
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
     * @Given I am reporting on :moneyInType
     */
    public function iAmReportingOnMoneyInType(string $moneyInType)
    {
        if (false !== strpos($moneyInType, ',')) {
            $moneyInTypeArray = explode(', ', $moneyInType);
        }

        if (empty($moneyInTypeArray)) {
            $optionIndex = array_search($moneyInType, $this->moneyInShortTypeDictionary);
            $this->moneyInShortList[] = $this->moneyInShortTypeDictionary[$optionIndex];
            $this->checkOption("money_short[moneyShortCategoriesIn][{$optionIndex}][present]");
        } else {
            foreach ($moneyInTypeArray as $moneyInTypeValue) {
                $optionIndex = array_search($moneyInTypeValue, $this->moneyInShortTypeDictionary);
                $this->moneyInShortList[] = $this->moneyInShortTypeDictionary[$optionIndex];
                $this->checkOption("money_short[moneyShortCategoriesIn][{$optionIndex}][present]");
            }
        }

        $this->pressButton('Save and continue');
    }

    /**
     * @Given I don't have a one off payment
     */
    public function iDontHaveAOneOffPayment()
    {
        $this->selectOption('yes_no[moneyTransactionsShortInExist]', 'no');
        $this->pressButton('Save and continue');
    }

    /**
     * @Given I have a single one-off payments over £1k
     */
    public function iHaveASingleOneOffPaymentOver1k()
    {
        $this->selectOption('yes_no[moneyTransactionsShortInExist]', 'yes');
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'yes_no_save');

        $this->addMoneyOutPayment('Lorem ipsum', '1500');

        $this->selectOption('add_another[addAnother]', 'no');
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'add_another_save');
    }

    /**
     * @When I edit an existing money in short payment
     */
    public function iEditOneOffMoneyInPayment()
    {
        $this->iVisitMoneyInShortSummarySection();
        $this->iAmOnMoneyInShortSummaryPage();
        $urlRegex = sprintf('/%s\/.*\/money-in-short\/exist.*$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);

        $this->selectOption('yes_no[moneyTransactionsShortInExist]', 'yes');
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'yes_no_save');

        $this->addMoneyOutPayment('Lorem ipsum', '1500', '08/12/2021');

        $this->selectOption('add_another[addAnother]', 'no');
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
        $urlRegex = sprintf('/%s\/.*\/money-in-short\/exist.*$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);

        $this->selectOption('yes_no[moneyTransactionsShortInExist]', 'yes');
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'yes_no_save');

        $this->addMoneyOutPayment('Lorem upsum', '10', '05/05/2015');
    }

    /**
     * @Then I should the see correct validation message
     */
    public function iShouldSeeTheCorrectValidationMessage()
    {
        $this->assertOnAlertMessage('Please input a value of at least £1,000');
    }

    /**
     * @param string $description description of the pne off payment
     * @param string $amount      amount for the one off payment
     * @param string $date        date the money came in (optional) format: DD/MM/YYYY
     */
    private function addMoneyOutPayment(string $description, string $amount, string $date = null)
    {
        $this->iAmOnMoneyInShortAddPage();

        $oneOffPayment = [
            'description' => $description,
            'amount' => $amount,
        ];

        $this->fillField('money_short_transaction[description]', $description);
        $this->fillField('money_short_transaction[amount]', $amount);

        if (null !== $date) {
            $explodedDate = explode('/', $date);
            $this->fillField('money_short_transaction[date][day]', $explodedDate[0]);
            $this->fillField('money_short_transaction[date][month]', $explodedDate[1]);
            $this->fillField('money_short_transaction[date][year]', $explodedDate[2]);

            $oneOffPayment['day'] = $explodedDate[0];
            $oneOffPayment['month'] = $explodedDate[1];
            $oneOffPayment['year'] = $explodedDate[2];
        }

        $this->moneyInShortOneOff[] = $oneOffPayment;
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'money_short_transaction_save');
    }

    /**
     * @Then I should see the expected money in section summary
     */
    public function iShouldSeeTheExpectedMoneyInSectionSummary()
    {
        $this->iAmOnMoneyInShortSummaryPage();

        if (count($this->moneyInShortList) > 0) {
            $moneyInShortListWrapper[] = $this->moneyInShortList;
        } else {
            $moneyInShortListWrapper[] = ['none'];
        }

        $this->expectedResultsDisplayed(0, $moneyInShortListWrapper, 'Money in categories entered');

        if (count($this->moneyInShortOneOff) > 0) {
            $oneOffPaymentsWrapper[] = ['yes'];
        } else {
            $oneOffPaymentsWrapper[] = ['no'];
        }

        $this->expectedResultsDisplayed(1, $oneOffPaymentsWrapper, 'Answers for "One off payments"');

        if (count($this->moneyInShortOneOff) > 0) {
            $expectedOneOffPayments = $this->moneyInShortOneOff;
            foreach ($expectedOneOffPayments as $key => $oneOffPayment) {
                $expectedOneOffPayments[$key]['amount'] = '£'.$this->moneyFormat($this->moneyInShortOneOff[$key]['amount']);

                if (null !== $expectedOneOffPayments[$key]['day'] && null !== $expectedOneOffPayments[$key]['month'] && null !== $expectedOneOffPayments[$key]['year']) {
                    $dateTimestamp = sprintf(
                        '%s-%s-%s 00:00',
                        $expectedOneOffPayments[$key]['year'],
                        $expectedOneOffPayments[$key]['month'],
                        $expectedOneOffPayments[$key]['day'],
                    );

                    $date = date('j F Y', strtotime($dateTimestamp));
                    array_splice($expectedOneOffPayments[$key], 1, 0, $date);
                }

                // unset values
                unset($expectedOneOffPayments[$key]['day']);
                unset($expectedOneOffPayments[$key]['month']);
                unset($expectedOneOffPayments[$key]['year']);

                $this->moneyInShortTotal += floatval($this->moneyInShortOneOff[$key]['amount']);
            }

            // Check the individual one off payments
            $expectedOneOffPayments = array_values($expectedOneOffPayments);
            $this->expectedResultsDisplayed(2, $expectedOneOffPayments, 'One of payments details');
            // Check the total
            $this->expectedResultsDisplayed(3, [['£'.$this->moneyFormat($this->moneyInShortTotal)]], 'One of payments total');
        }
    }
}
