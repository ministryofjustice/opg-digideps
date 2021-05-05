<?php

declare(strict_types=1);

namespace DigidepsBehat\v2\Reporting\Sections;

trait MoneyOutShortSectionTrait
{
    private array $oneOffPaymentsList = [];
    private array $categoryList = [];

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
        $this->iAmOnMoneyOutShortExistsPage();
        $this->selectOption('yes_no[moneyTransactionsShortOutExist]', 'no');
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'yes_no_save');
    }

    /**
     * @When I add all the categories of money paid out
     */
    public function iAddAllTheCategoriesOfMoneyPaidOut()
    {
        $this->iAmOnMoneyOutShortCategoryPage();
//        var_dump($this->getSession()->getPage()->getHtml());
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
        $this->iAmOnMoneyOutShortExistsPage();
        $this->selectOption('yes_no[moneyTransactionsShortOutExist]', 'yes');
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'yes_no_save');
        $this->iAmOnMoneyOutShortAddPage();
        $this->fillField('money_short_transaction[description]', 'Payment1 Big Expense');
        $this->fillField('money_short_transaction[amount]', '101');
    }

    /**
     * @Then I should see the expected money out section summary
     */
    public function iShouldSeeTheExpectedMoneyOutSummary()
    {
        $this->iAmOnMoneyOutShortSummaryPage();

        $tableBodies = $this->getSession()->getPage()->findAll('css', 'dl');

        if (!$tableBodies) {
            $this->throwContextualException('A dl element was not found on the page');
        }

        $category = $tableBodies[0];
        $oneOffPayments = $tableBodies[1];

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

        $oneOffPaymentRows = $oneOffPayments->findAll('css', 'div.govuk-summary-list__row');

        if (!$oneOffPaymentRows) {
            $this->throwContextualException('A div element was not found on the page');
        }

        if (count($this->oneOffPaymentsList) < 1) {
            $this->assertStringContainsString('No', $oneOffPaymentRows[1]->getHtml(), 'Short Money Out One Off Payments');
        } else {
            foreach ($this->oneOffPaymentsList as $expectedoneOffPaymentKey => $expectedoneOffPayment) {
                $this->assertStringContainsString($expectedoneOffPayment, $categoryRows[$expectedoneOffPaymentKey]->getHtml(), 'Short Money Out One Off Payments');
            }
        }
    }
}
