<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use Behat\Gherkin\Node\TableNode;

trait MoneyInShortSectionTrait
{
    private array $moneyInShortTypeDictionary = [
        0 => 'State pension and benefits',
        1 => 'Bequests - for example, inheritance, gifts received',
        2 => 'Income from investments, dividends, property rental',
        3 => 'Sale of investments, property or assets',
        4 => 'Salary or wages',
        5 => 'Compensations and damages awards',
        6 => 'Personal pension',
    ];
    private array $moneyInShortOneOff = [];
    private array $paymentNumber = [];

    /**
     * @When I view and start the money in short report section
     */
    public function iViewAndStartMoneyInShortSection()
    {
        $this->iVisitMoneyInShortSection();
        $this->pressButton('Start money in');
    }

    /**
     * @Given /^I answer "([^"]*)" to adding money in on the clients behalf$/
     */
    public function iAnswerToAddingMoneyInOnTheClientsBehalf($arg1)
    {
        $this->chooseOption('does_money_in_exist[moneyInExists]', $arg1, 'moneyInExists');
        $this->pressButton('Save and continue');
    }

    /**
     * @Given /^I answer "([^"]*)" to having one off payments over 1k$/
     */
    public function iAnswerToHavingOneOffPaymentsOver1k($arg1)
    {
        $this->chooseOption('yes_no[moneyTransactionsShortInExist]', $arg1, 'one-off-payments');
        $this->pressButton('Save and continue');
    }

    /**
     * @Given I have no payments going out
     */
    public function iHaveNoPaymentsGoingOut()
    {
        $this->iAmOnMoneyInShortCategoryPage();
        $this->pressButton('Save and continue');
        $this->iAnswerToHavingOneOffPaymentsOver1k('no');
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
                'money-types',
                $this->moneyInShortTypeDictionary[$optionIndex]
            );
        }

        $this->pressButton('Save and continue');
    }

    /**
     * @Given /^I answer "([^"]*)" to one off payments over £1k$/
     */
    public function iAnswerToOneOffPaymentsOver£1k($arg1)
    {
        $this->chooseOption(
            'yes_no[moneyTransactionsShortInExist]',
            $arg1,
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

        $this->addMoneyInPayment('Lorem ipsum', 1500);

        $this->chooseOption('add_another[addAnother]', 'no');
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'add_another_save');
    }

    /**
     * @Given /^I add (\d+) one\-off payments over £1k$/
     */
    public function iAddAOneOffPaymentsOver£1k(int $numberOfPayments)
    {
        $this->iAmOnMoneyInShortOneOffPaymentsExistsPage();

        $this->chooseOption(
            'yes_no[moneyTransactionsShortInExist]',
            'yes',
            'one-off-payments'
        );

        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'yes_no_save');

        $paymentsRange = range(1, $numberOfPayments);

        foreach ($paymentsRange as $paymentNumber) {
            $this->addMoneyInPayment(sprintf('lorem ipsum %s', rand(1, 10)), rand(1000, 10000), $paymentNumber);
            $this->paymentNumber[] = $paymentNumber;
            $this->addAnotherMoneyInPayment($numberOfPayments === $paymentNumber ? 'no' : 'yes');
        }

        $this->iAmOnMoneyInShortSummaryPage();
    }

    /**
     * @When I edit the money in short section and add a payment
     */
    public function iEditTheMoneyInShortSectionAndAddAPayment()
    {
        $this->iVisitMoneyInShortSummarySection();
        $this->iAmOnMoneyInShortSummaryPage();
        $urlRegex = sprintf('/%s\/.*\/money-in-short\/exist\?from\=summary$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);

        $this->iAnswerToAddingMoneyInOnTheClientsBehalf('Yes');
        $this->iClickSaveAndContinue();

        $this->chooseOption('yes_no[moneyTransactionsShortInExist]', 'yes', 'one-off-payments');
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'yes_no_save');

        $this->addMoneyInPayment('Lorem ipsum', 1500, 1, '08/12/2021');

        $this->chooseOption('add_another[addAnother]', 'no');
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'add_another_save');

        $this->iAmOnMoneyInShortSummaryPage();
    }

    /**
     * @When /^I edit the money in short "([^"]*)" summary section$/
     */
    public function iEditTheMoneyInShortSummarySection($arg)
    {
        $this->iAmOnMoneyInShortSummaryPage();

        // clean data to correctly track expected results when user edits answers.
        $this->removeSection('moneyInExists');
        $this->removeSection('money-types');
        $this->removeSection('one-off-payments');
        $this->removeSection('reasonForNoMoneyIn');

        foreach ($this->paymentNumber as $payment) {
            $this->removeSection('moneyInDetails'.$payment);
        }

        $urlRegex = sprintf('/%s\/.*\/money-in-short\/%s\?from\=summary$/', $this->reportUrlPrefix, $arg);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
    }

    /**
     * @When I add a one off money in payment that is less than £1k
     */
    public function iAddAOneOffMoneyInPaymentThatIsLessThan1k()
    {
        $this->iVisitMoneyInShortSummarySection();
        $urlRegex = sprintf('/%s\/.*\/money-in-short\/exist\?from\=summary$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);

        $this->iAnswerToAddingMoneyInOnTheClientsBehalf('Yes');
        $this->iClickSaveAndContinue();

        $this->chooseOption('yes_no[moneyTransactionsShortInExist]', 'yes', 'one-off-payments');
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'yes_no_save');

        $this->addMoneyInPayment('Lorem upsum', 10, 1, '05/05/2015');
    }

    /**
     * @Then I should the see correct validation message
     */
    public function iShouldSeeTheCorrectValidationMessage()
    {
        $this->assertOnAlertMessage('The amount must be between £1000 and £100,000,000,000');
    }

    /**
     * @param string      $description description of the pne off payment
     * @param int         $amount      amount for the one off payment
     * @param string|null $date        date the money came in (optional) format: DD/MM/YYYY
     */
    private function addMoneyInPayment(string $description, int $amount, int $paymentCount, string $date = null)
    {
        $this->iAmOnMoneyInShortAddPage();

        $this->fillInField('money_short_transaction[description]', $description, 'moneyInDetails'.$paymentCount);
        $this->fillInFieldTrackTotal('money_short_transaction[amount]', $amount, 'moneyInDetails'.$paymentCount);

        if (null !== $date) {
            $explodedDate = explode('/', $date);

            $this->fillInDateFields(
                'money_short_transaction[date]',
                intval($explodedDate[0]),
                intval($explodedDate[1]),
                intval($explodedDate[2]),
                'moneyInDetails'.$paymentCount
            );
        }
        $this->moneyInShortOneOff[] = [$description => $amount];

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

    /**
     * @Then /^I enter a reason for no money in short$/
     */
    public function iEnterAReasonForNoMoneyInShort()
    {
        $this->iAmOnNoMoneyInShortExistsPage();

        $this->fillInField('reason_for_no_money[reasonForNoMoneyIn]', 'No money in', 'reasonForNoMoneyIn');
        $this->pressButton('Save and continue');
    }

    /**
     * @Then /^there should be "([^"]*)" one off payments displayed on the money in summary page$/
     */
    public function thereShouldBeOneOffPaymentsDisplayedOnTheSummaryPage($arg1)
    {
        $this->iAmOnMoneyInShortSummaryPage();

        $oneOffPaymentTableRows = $this->getSession()->getPage()->find('xpath', "//tr[contains(@class,'behat-region-transaction-')]");

        if ('no' == $arg1) {
            $this->assertPageNotContainsText('List of items of income over £1000');
            $this->assertIsNull($oneOffPaymentTableRows, 'One off payment rows are not rendered');

            $this->expectedResultsDisplayedSimplified(null, true, false, false, false);
        } else {
            $this->assertPageContainsText('List of items of income over £1000');

            foreach ($this->moneyInShortOneOff as $transactionItems) {
                foreach ($transactionItems as $description => $value) {
                    $this->assertElementContainsText('table', $description);
                    $this->assertElementContainsText('table', '£'.number_format($value, 2));
                }
            }
            $this->expectedResultsDisplayedSimplified();
        }
    }

    /**
     * @Given /^I delete the transaction from the summary page$/
     */
    public function iDeleteTheTransactionFromTheSummaryPage()
    {
        $this->iVisitMoneyInShortSummarySection();

        $this->removeAnswerFromSection(
            'money_short_transaction[amount]',
            'moneyInDetails1',
            true,
            'Yes, remove item of income'
        );

        $count = 0;
        foreach ($this->paymentNumber as $payment) {
            $this->getSectionAnswers('moneyInDetails'.$payment) ? $count++ : $count;
        }
        if (0 == $count) {
            $this->removeSection('one-off-payments');
            $this->updateExpectedAnswerInSection('yes_no[moneyTransactionsShortInExist]', 'one-off-payments', 'no');
        }

        $this->moneyInShortOneOff = [];

        $this->iAmOnMoneyInShortSummaryPage();
    }

    /**
     * @Then I should be on the delete page
     */
    private function iShouldBeOnTheMoneyInShortDeletePage(): bool
    {
        return $this->iAmOnPage(sprintf('/report\/.*\/money-in-short\/.*\/delete$/'));
    }

    /**
     * @Then /^I edit the answer to the money in one off payment over 1k$/
     */
    public function iEditTheAnswerToTheOneOffPaymentsOver1K()
    {
        $this->removeSection('one-off-payments');

        $urlRegex = sprintf('/%s\/.*\/money-in-short\/oneOffPaymentsExist\?from\=summary$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
    }

    private function addAnotherMoneyInPayment($selection)
    {
        $this->iAmOnMoneyInShortAddAnotherPage();
        $this->selectOption('add_another[addAnother]', $selection);
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'add_another_save');
    }
}
