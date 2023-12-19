<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait MoneyOutShortSectionTrait
{
    private array $moneyTypesDictionary = [
        0 => 'Accommodation costs – for example, rent, mortgage, service charges',
        1 => 'Care fees or local authority charges for care',
        2 => 'Holidays and trips',
        3 => 'Household bills – for example, water, gas, electricity, phone, council tax',
        4 => '%s\'s personal allowance',
        5 => 'Professional fees – for example, solicitor or accountant fees',
        6 => 'New investments – for example, buying shares, new bonds',
        7 => 'Travel costs – for example, bus, train, taxi fares',
    ];

    /**
     * @When I view and start the money out short report section
     */
    public function iViewAndStartMoneyOutShortSection()
    {
        $this->iVisitMoneyOutShortSection();
        $this->clickLink('Start money out');

        $this->moneyTypesDictionary[4] = sprintf(
            $this->moneyTypesDictionary[4],
            $this->loggedInUserDetails->getClientFirstName()
        );
    }

    /**
     * @Given /^I answer "([^"]*)" to taking money out on the clients behalf$/
     */
    public function iAnswerToTakingMoneyOutOnTheClientsBehalf($arg1)
    {
        $this->chooseOption('does_money_out_exist[moneyOutExists]', $arg1);
        $this->pressButton('Save and continue');
    }

    /**
     * @When I have made no payments out
     */
    public function iHaveMadeNoPaymentsOut()
    {
        $this->iAmOnMoneyOutShortCategoryPage();

        $this->pressButton('Save and continue');

        $this->iAnswerNoOneOffPaymentsOver1k();
    }

    /**
     * @When I answer that there are not any one-off payments over £1k
     */
    public function iAnswerNoOneOffPaymentsOver1k()
    {
        $this->oneOffPaymentOver1kExists('no');
    }

    /**
     * @When I add one category of money paid out
     */
    public function iAddOneCategoryOfMoneyPaidOut()
    {
        $this->iAmOnMoneyOutShortCategoryPage();

        $this->tickCheckbox(
            'moneyTypes',
            'money_short[moneyShortCategoriesOut][0][present]',
            'haveMadePayment',
            $this->moneyTypesDictionary[0]
        );

        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'money_short_save');
    }

    /**
     * @When I add all the categories of money paid out
     */
    public function iAddAllTheCategoriesOfMoneyPaidOut()
    {
        $this->iAmOnMoneyOutShortCategoryPage();

        foreach ($this->moneyTypesDictionary as $index => $moneyType) {
            $this->tickCheckbox(
                'moneyTypes',
                "money_short[moneyShortCategoriesOut][$index][present]",
                'haveMadePayment',
                $moneyType
            );
        }

        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'money_short_save');
    }

    /**
     * @When I answer that there are :numberOfPayments one-off payments over £1k
     */
    public function iAnswerNumberOneOffPaymentsOver1k(int $numberOfPayments)
    {
        $this->oneOffPaymentOver1kExists('yes');

        $paymentsRange = range(1, $numberOfPayments);

        foreach ($paymentsRange as $paymentNumber) {
            $this->addAMoneyOutPayment($this->faker->sentence(mt_rand(4, 20)), mt_rand(1000, 2000), 1, 2, 2019, $paymentNumber);
            $this->addAnotherMoneyOutPayment($numberOfPayments === $paymentNumber ? 'no' : 'yes');
        }

        $this->iAmOnMoneyOutShortSummaryPage();
    }

    /**
     * @When I remove an existing money out short payment
     */
    public function iRemoveOneOffPayment()
    {
        $this->iVisitMoneyOutShortSummarySection();

        $this->removeAnswerFromSection(
            'money_short_transaction[amount]',
            'moneyOutDetails1',
            true,
            'Yes, remove payment'
        );

        $this->iAmOnMoneyOutShortSummaryPage();
    }

    /**
     * @When I edit an existing money out short payment
     */
    public function iEditOneOffShortPayment()
    {
        $this->iVisitMoneyOutShortSummarySection();
        $this->iAmOnMoneyOutShortSummaryPage();

        $formattedAmount = $this->normalizeIntToCurrencyString($this->getSectionTotal('moneyOutDetails1'));
        $locator = sprintf("//td[normalize-space()='%s']/..", $formattedAmount);
        $paymentRow = $this->getSession()->getPage()->find('xpath', $locator);

        $this->editFieldAnswerInSection($paymentRow, 'money_short_transaction[description]', $this->faker->sentence(mt_rand(2, 15)), 'moneyOutDetails1', false);
        $this->editFieldAnswerInSectionTrackTotal($paymentRow, 'money_short_transaction[amount]', 'moneyOutDetails1', false, 1001);

        $this->iAmOnMoneyOutShortSummaryPage();
    }

    /**
     * @When I add a payment and state no further payments
     */
    public function iAddAPaymentAndStateNoFurtherPayments()
    {
        $this->iAddOneCategoryOfMoneyPaidOut();

        $this->iAnswerNumberOneOffPaymentsOver1k(1);
    }

    /**
     * @When I change my mind and add another payment
     */
    public function iChangeMindADdAnotherPayment()
    {
        $this->clickLink('Add an payment over £1,000');
        $this->addAMoneyOutPayment($this->faker->sentence(mt_rand(4, 20)), mt_rand(1000, 2000), 1, 2, 2019, 2);
        $this->addAnotherMoneyOutPayment('no');
    }

    /**
     * @When I answer that there are 1 one-off payments over £1k but add a payment of less than £1K
     */
    public function iAnswerNumberOneOffPaymentsOver1kButAddTooLowPayment()
    {
        $this->iAddOneCategoryOfMoneyPaidOut();

        $this->oneOffPaymentOver1kExists('yes');

        $this->addAMoneyOutPayment($this->faker->sentence(mt_rand(4, 20)), mt_rand(1, 999), 1, 2, 2019, 1);
    }

    /**
     * @Then I should see correct validation message
     */
    public function iShouldSeeCorrectValidationMessage()
    {
        $this->assertOnAlertMessage('Please input a value of at least £1,000');
    }

    /**
     * @Then I should see the expected money out section summary
     */
    public function iShouldSeeTheExpectedMoneyOutSummary()
    {
        $this->iAmOnMoneyOutShortSummaryPage();

        $this->expectedResultsDisplayedSimplified();
    }

    private function oneOffPaymentOver1kExists($selection)
    {
        $this->iAmOnMoneyOutShortOneOffPaymentsExistsPage();

        $this->chooseOption('yes_no[moneyTransactionsShortOutExist]', $selection, 'over1K');
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'yes_no_save');
    }

    private function addAMoneyOutPayment(string $description, int $amount, int $day, int $month, int $year, int $paymentCount)
    {
        $this->iAmOnMoneyOutShortAddPage();

        $this->fillInField('money_short_transaction[description]', $description, 'moneyOutDetails'.$paymentCount);
        $this->fillInFieldTrackTotal('money_short_transaction[amount]', $amount, 'moneyOutDetails'.$paymentCount);
        $this->fillInDateFields('money_short_transaction[date]', $day, $month, $year, 'moneyOutDetails'.$paymentCount);

        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'money_short_transaction_save');
    }

    private function addAnotherMoneyOutPayment($selection)
    {
        $this->iAmOnMoneyOutShortAddAnotherPage();
        $this->selectOption('add_another[addAnother]', $selection);
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'add_another_save');
    }
}
