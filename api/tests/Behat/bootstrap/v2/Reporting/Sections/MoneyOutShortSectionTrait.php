<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait MoneyOutShortSectionTrait
{
    private array $oneOffPaymentsList = [];
    private array $categoryList = [];
    private float $oneOffPaymentsTotal = 0.0;

    /**
     * @When I view and start the money out report section
     */
    public function iViewAndStartMoneyOutShortSection()
    {
        $this->iVisitMoneyOutShortSection();
        $this->clickLink('Start money out');
    }

    /**
     * @When I have made no payments out
     */
    public function iHaveMadeNoPaymentsOut()
    {
        $this->iAmOnMoneyOutShortCategoryPage();
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'money_short_save');
        $this->iAnswerNoOneOffPaymentsOver1k();
    }

    /**
     * @When I add some categories of money paid out
     */
    public function iAddSomeCategoriesOfMoneyOut()
    {
        $this->iAmOnMoneyOutShortCategoryPage();
        $this->getSession()->getPage()->selectFieldOption('money_short[moneyShortCategoriesOut][0][present]', '1');
        $this->categoryList[] = 'accommodation costs';
        $this->getSession()->getPage()->selectFieldOption('money_short[moneyShortCategoriesOut][3][present]', '1');
        $this->categoryList[] = 'household bills';
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'money_short_save');
    }

    /**
     * @When I answer that there are no one-off payments over £1k
     */
    public function iAnswerNoOneOffPaymentsOver1k()
    {
        $this->oneOffPaymentOver1kExists('no');
    }

    /**
     * @When I add all the categories of money paid out
     */
    public function iAddAllTheCategoriesOfMoneyPaidOut()
    {
        $this->iAmOnMoneyOutShortCategoryPage();
        $this->getSession()->getPage()->selectFieldOption('money_short[moneyShortCategoriesOut][0][present]', '1');
        $this->categoryList[] = 'accommodation costs';
        $this->getSession()->getPage()->selectFieldOption('money_short[moneyShortCategoriesOut][1][present]', '1');
        $this->categoryList[] = 'care fees';
        $this->getSession()->getPage()->selectFieldOption('money_short[moneyShortCategoriesOut][2][present]', '1');
        $this->categoryList[] = 'holidays and trips';
        $this->getSession()->getPage()->selectFieldOption('money_short[moneyShortCategoriesOut][3][present]', '1');
        $this->categoryList[] = 'household bills';
        $this->getSession()->getPage()->selectFieldOption('money_short[moneyShortCategoriesOut][4][present]', '1');
        $this->categoryList[] = 'personal allowance';
        $this->getSession()->getPage()->selectFieldOption('money_short[moneyShortCategoriesOut][5][present]', '1');
        $this->categoryList[] = 'professional fees';
        $this->getSession()->getPage()->selectFieldOption('money_short[moneyShortCategoriesOut][6][present]', '1');
        $this->categoryList[] = 'new investments';
        $this->getSession()->getPage()->selectFieldOption('money_short[moneyShortCategoriesOut][7][present]', '1');
        $this->categoryList[] = 'travel costs';
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'money_short_save');
    }

    /**
     * @When I answer that there are a couple of one-off payments over £1k
     */
    public function iAnswerTwoOneOffPaymentsOver1k()
    {
        $this->oneOffPaymentOver1kExists('yes');
        $this->addAMoneyOutPayment('test_payment_1', '1001', '01', '02', '2019');
        $this->addAnotherMoneyOutPayment('yes');
        $this->addAMoneyOutPayment('test_payment_2', '1002', '03', '04', '2020');
        $this->addAnotherMoneyOutPayment('no');
        $this->iAmOnMoneyOutShortSummaryPage();
    }

    /**
     * @When I remove an existing money out payment
     */
    public function iRemoveOneOffPayment()
    {
        $this->iVisitMoneyOutShortSummarySection();
        $this->iAmOnMoneyOutShortSummaryPage();
        $urlRegex = sprintf('/%s\/.*\/money-out-short\/exist.*$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
        $this->oneOffPaymentOver1kExists('yes');
        $this->addAMoneyOutPayment('to_remove_1', '1003', '10', '11', '2019');
        $this->addAnotherMoneyOutPayment('yes');
        $this->addAMoneyOutPayment('to_remove_2', '1004', '11', '12', '2020');
        $this->addAnotherMoneyOutPayment('no');
        $this->iAmOnMoneyOutShortSummaryPage();
        $this->iRemoveAOneOffPayment(0);
        $this->iAmOnMoneyOutShortSummaryPage();
    }

    /**
     * @When I edit an existing money out payment
     */
    public function iEditOneOffPayment()
    {
        $this->iVisitMoneyOutShortSummarySection();
        $this->iAmOnMoneyOutShortSummaryPage();
        $urlRegex = sprintf('/%s\/.*\/money-out-short\/exist.*$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
        $this->oneOffPaymentOver1kExists('yes');
        $this->addAMoneyOutPayment('to_edit_1', '1004', '08', '10', '2019');
        $this->addAnotherMoneyOutPayment('no');
        $this->iAmOnMoneyOutShortSummaryPage();
        $this->iEditAOneOffPayment(0, 'to_edit_2', '1005', '09', '11', '2020');
        $this->iAmOnMoneyOutShortSummaryPage();
    }

    /**
     * @When I add a payment and state no further payments
     */
    public function iAddAPaymentAndStateNoFurtherPayments()
    {
        $this->iVisitMoneyOutShortSummarySection();
        $this->iAmOnMoneyOutShortSummaryPage();
        $urlRegex = sprintf('/%s\/.*\/money-out-short\/exist.*$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
        $this->oneOffPaymentOver1kExists('yes');
        $this->addAMoneyOutPayment('payment_1', '1006', '01', '01', '2018');
        $this->addAnotherMoneyOutPayment('no');
    }

    /**
     * @When I change my mind and add another payment
     */
    public function iChangeMyMindAndAddAnotherPayment()
    {
        $this->iAmOnMoneyOutShortSummaryPage();
        $urlRegex = sprintf('/%s\/.*\/money-out-short\/add.*$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
        $this->addAMoneyOutPayment('payment_1', '1006', '01', '01', '2018');
        $this->addAnotherMoneyOutPayment('no');
        $this->iAmOnMoneyOutShortSummaryPage();
    }

    /**
     * @When I add a one off payment of less than £1k
     */
    public function iAddAOneOffPaymentOfLessThan1k()
    {
        $this->iVisitMoneyOutShortSummarySection();
        $this->iAmOnMoneyOutShortSummaryPage();
        $urlRegex = sprintf('/%s\/.*\/money-out-short\/exist.*$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
        $this->oneOffPaymentOver1kExists('yes');
        $this->addAMoneyOutPayment('payment_1', '10', '01', '01', '2018');
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
        $descriptionListItems = $this->findAllCssElements('dl');
        $category = $descriptionListItems[0];
        $this->checkCategories($category);

        $oneOffPayments = $descriptionListItems[1];
        $this->checkOneOffPaymentsExist($oneOffPayments);

        if (count($this->oneOffPaymentsList) > 0) {
            $oneOffPaymentTBodyRows = $this->findAllCssElements('tbody');
            $this->checkOneOffPaymentRows($oneOffPaymentTBodyRows);
        }
    }

    private function iEditAOneOffPayment($paymentOccurrence, $description, $amount, $day, $month, $year)
    {
        // Click on the nth row to delete
        $urlRegex = sprintf('/%s\/.*\/money-out-short\/edit\/[0-9].*$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, $paymentOccurrence);
        $this->iAmOnMoneyOutShortEditPage();

        // Remove the payment from our array
        $this->oneOffPaymentsList[$paymentOccurrence]['description'] = $description;
        $this->oneOffPaymentsList[$paymentOccurrence]['amount'] = $amount;
        $this->oneOffPaymentsList[$paymentOccurrence]['day'] = $day;
        $this->oneOffPaymentsList[$paymentOccurrence]['month'] = $month;
        $this->oneOffPaymentsList[$paymentOccurrence]['year'] = $year;
        $this->oneOffPaymentsList = array_values($this->oneOffPaymentsList);

        $this->fillField('money_short_transaction[description]', $description);
        $this->fillField('money_short_transaction[amount]', $amount);
        $this->fillField('money_short_transaction[date][day]', $day);
        $this->fillField('money_short_transaction[date][month]', $month);
        $this->fillField('money_short_transaction[date][year]', $year);
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'money_short_transaction_save');
    }

    private function iRemoveAOneOffPayment($paymentOccurrence)
    {
        // Click on the nth row to delete
        $urlRegex = sprintf('/%s\/.*\/money-out-short\/.*\/delete$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, $paymentOccurrence);

        // Remove the payment from our array
        unset($this->oneOffPaymentsList[$paymentOccurrence]);
        $this->oneOffPaymentsList = array_values($this->oneOffPaymentsList);

        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'confirm_delete_confirm');
    }

    private function oneOffPaymentOver1kExists($selection)
    {
        $this->iAmOnMoneyOutShortExistsPage();
        $this->selectOption('yes_no[moneyTransactionsShortOutExist]', $selection);
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'yes_no_save');
    }

    private function addAMoneyOutPayment($description, $amount, $day, $month, $year)
    {
        $oneOffPayment = [
            'description' => $description,
            'amount' => $amount,
            'day' => $day,
            'month' => $month,
            'year' => $year,
        ];

        $this->oneOffPaymentsList[] = $oneOffPayment;

        $this->iAmOnMoneyOutShortAddPage();
        $this->fillField('money_short_transaction[description]', $description);
        $this->fillField('money_short_transaction[amount]', $amount);
        $this->fillField('money_short_transaction[date][day]', $day);
        $this->fillField('money_short_transaction[date][month]', $month);
        $this->fillField('money_short_transaction[date][year]', $year);
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'money_short_transaction_save');
    }

    private function addAnotherMoneyOutPayment($selection)
    {
        $this->iAmOnMoneyOutShortAddAnotherPage();
        $this->selectOption('add_another[addAnother]', $selection);
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'add_another_save');
    }

    private function moneyFormat($value)
    {
        return number_format(floatval($value), 2, '.', ',');
    }

    private function checkCategories($category)
    {
        $categoryRows = $category->findAll('css', 'div.govuk-summary-list__row');

        if (!$categoryRows) {
            $this->throwContextualException('A div element was not found on the page');
        }

        if (count($this->categoryList) < 1) {
            $this->assertStringContainsString('None', $categoryRows[1]->getHtml(), 'Short Money Out Categories');
        } else {
            $categoryListItems = $categoryRows[1]->findAll('css', 'li');
            foreach ($this->categoryList as $expectedCategoryKey => $expectedCategory) {
                $this->assertStringContainsString($expectedCategory, $categoryListItems[$expectedCategoryKey]->getHtml(), 'Short Money Out Categories');
            }
        }
    }

    private function checkOneOffPaymentsExist($oneOffPayments)
    {
        $oneOffPaymentYesNo = $oneOffPayments->findAll('css', 'div.govuk-summary-list__row');

        if (!$oneOffPaymentYesNo) {
            $this->throwContextualException('A div element was not found on the page');
        }

        if (count($this->oneOffPaymentsList) < 1) {
            $this->assertStringContainsString('No', $oneOffPaymentYesNo[1]->getHtml(), 'Short Money Out One Off Payments');
        } else {
            $this->assertStringContainsString('Yes', $oneOffPaymentYesNo[1]->getHtml(), 'Short Money Out One Off Payments');
        }
    }

    private function checkOneOffPaymentRows($oneOffPaymentTBodyRows)
    {
        $oneOffPaymentRows = $oneOffPaymentTBodyRows[0]->findAll('css', 'tr');
        $oneOffPaymentTotalRow = $oneOffPaymentTBodyRows[1];

        foreach ($oneOffPaymentRows as $oneOffPaymentRowKey => $oneOffPaymentRow) {
            $dateTimestamp = sprintf(
                '%s-%s-%s 00:00',
                $this->oneOffPaymentsList[$oneOffPaymentRowKey]['year'],
                $this->oneOffPaymentsList[$oneOffPaymentRowKey]['month'],
                $this->oneOffPaymentsList[$oneOffPaymentRowKey]['day']
            );

            $date = date('j F Y', strtotime($dateTimestamp));
            $amountFormatted = $this->moneyFormat($this->oneOffPaymentsList[$oneOffPaymentRowKey]['amount']);

            $this->assertStringContainsString(
                $this->oneOffPaymentsList[$oneOffPaymentRowKey]['description'],
                $oneOffPaymentRow->getHtml(),
                'Short Money Out One Off Payments Items'
            );
            $this->assertStringContainsString($amountFormatted, $oneOffPaymentRow->getHtml(), 'Short Money Out One Off Payments Items');
            $this->assertStringContainsString($date, $oneOffPaymentRow->getHtml(), 'Short Money Out One Off Payments Items');

            $this->oneOffPaymentsTotal += floatval($this->oneOffPaymentsList[$oneOffPaymentRowKey]['amount']);
        }

        $this->assertStringContainsString($this->moneyFormat($this->oneOffPaymentsTotal), $oneOffPaymentTotalRow->getHtml(), 'Short Money Out One Off Payments Total');
    }
}
