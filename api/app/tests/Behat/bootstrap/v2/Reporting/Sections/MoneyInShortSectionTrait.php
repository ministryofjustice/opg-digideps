<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use Behat\Gherkin\Node\TableNode;

trait MoneyInShortSectionTrait
{
    private int $moneyInPaymentCount = 0;
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
     * @Given /^I add "([^"]*)" one off payments over £1k$/
     */
    public function iAddAOneOffPaymentsOver£1k($numberOfPayments)
    {
        $this->iAmOnMoneyInShortOneOffPaymentsExistsPage();

        $this->chooseOption(
            'yes_no[moneyTransactionsShortInExist]',
            'yes',
            'one-off-payments'
        );

        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'yes_no_save');

        $i = 0;
        while ($i <= intval($numberOfPayments)) {
            $this->addMoneyInPayment(sprintf('lorem ipsum %s', rand(1, 10)), rand(1000, 10000));

            $this->iAmOnMoneyInShortAddAnotherPage();

            if ($i == $numberOfPayments - 1) {
                break;
            }

            $this->chooseOption('add_another[addAnother]', 'yes');
            $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'add_another_save');
            ++$i;
        }

        $this->chooseOption('add_another[addAnother]', 'no');
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'add_another_save');
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

        $this->addMoneyInPayment('Lorem ipsum', 1500, '08/12/2021');

        $this->chooseOption('add_another[addAnother]', 'no');
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'add_another_save');

        $this->iAmOnMoneyInShortSummaryPage();
    }

    /**
     * @When /^I edit the money in short "([^"]*)" summary section$/
     */
    public function iEditTheMoneyInShortSummarySection($arg)
    {
        $this->iVisitMoneyInShortSummarySection();
        $this->iAmOnMoneyInShortSummaryPage();

        // clean data to correctly track expected results when user edits answers.
        $this->removeSection('moneyInExists');
        $this->removeSection('haveMadePayment');
        $this->removeSection('over1K');
        $this->removeSection('reasonForNoMoneyIn');

        foreach ($this->paymentNumber as $payment) {
            $this->removeSection('moneyOutDetails'.$payment);
        }
        
        $urlRegex = sprintf('/%s\/.*\/money-in-short\/exist\?from\=summary$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
    }

    /**
     * @When I add a one off money in payment that is less than £1k
     */
    public function iAddAOneOffMoneyInPaymentThatIsLessThan1k()
    {
        $this->iVisitMoneyInShortSummarySection();
        $this->iAmOnMoneyInShortSummaryPage();
        $urlRegex = sprintf('/%s\/.*\/money-in-short\/exist\?from\=summary$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);

        $this->iAnswerToAddingMoneyInOnTheClientsBehalf('Yes');
        $this->iClickSaveAndContinue();

        $this->chooseOption('yes_no[moneyTransactionsShortInExist]', 'yes', 'one-off-payments');
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'yes_no_save');

        $this->addMoneyInPayment('Lorem upsum', 10, '05/05/2015');
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
    private function addMoneyInPayment(string $description, int $amount, string $date = null)
    {
        ++$this->moneyInPaymentCount;

        $this->iAmOnMoneyInShortAddPage();

        $this->fillInField('money_short_transaction[description]', $description, 'payment'.$this->moneyInPaymentCount);
        $this->fillInFieldTrackTotal('money_short_transaction[amount]', $amount, 'payment'.$this->moneyInPaymentCount);

        if (null !== $date) {
            $explodedDate = explode('/', $date);

            $this->fillInDateFields(
                'money_short_transaction[date]',
                intval($explodedDate[0]),
                intval($explodedDate[1]),
                intval($explodedDate[2]),
                'payment'.$this->moneyInPaymentCount
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
        $oneOffPaymentTableRows = $this->getSession()->getPage()->find('xpath', "//tr[contains(@class,'behat-region-transaction-')]");

        $this->iAmOnMoneyInShortSummaryPage();

        if ('no' == $arg1) {
            if ($this->getSectionAnswers('moneyTransactionsShortInExist')) {
                $this->expectedResultsDisplayedSimplified('moneyTransactionsShortInExist', true, false, false);
            }

            $this->assertPageNotContainsText('List of items of income over £1000');
            $this->assertIsNull($oneOffPaymentTableRows, 'One off payment rows are not rendered');
        } else {
            if ($this->getSectionAnswers('moneyTransactionsShortInExist')) {
                $this->expectedResultsDisplayedSimplified('moneyTransactionsShortInExist');
            }

            $this->assertPageContainsText('List of items of income over £1000');

            foreach ($this->moneyInShortOneOff as $transactionItems) {
                foreach ($transactionItems as $description => $value) {
                    $this->assertElementContainsText('table', $description);
                    $this->assertElementContainsText('table', '£'.number_format($value, 2));
                }
            }
        }
    }

    /**
     * @Given /^I delete the transaction from the summary page$/
     */
    public function iDeleteTheTransactionFromTheSummaryPage()
    {
        $this->clickLink('Remove');
        assert($this->iShouldBeOnTheMoneyInShortDeletePage());
        $this->pressButton('Yes, remove item of income');

        $this->moneyInShortOneOff = [];
    }

    /**
     * @Then I should be on the delete page
     */
    private function iShouldBeOnTheMoneyInShortDeletePage(): bool
    {
        return $this->iAmOnPage(sprintf('/report\/.*\/money-in-short\/.*\/delete$/'));
    }

    /**
     * @Then /^I edit the answer to the one off payments over 1k$/
     */
    public function iEditTheAnswerToTheOneOffPaymentsOver1k()
    {
        $urlRegex = sprintf('/%s\/.*\/money-in-short\/oneOffPaymentsExist\?from\=summary$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
    }
}
