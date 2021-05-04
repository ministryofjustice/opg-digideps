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
        $this->selectOption('yes_no[moneyTransactionsShortOutExist]', 'no');
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'yes_no_save');
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
        $this->categoryList[] = 'households bills';
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'money_short_save');
        $this->selectOption('yes_no[moneyTransactionsShortOutExist]', 'no');
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'yes_no_save');
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
            foreach ($this->categoryList as $expectedCategoryKey => $expectedCategory) {
                $this->assertStringContainsString($expectedCategory, $categoryRows[$expectedCategoryKey]->getHtml(), 'Short Money Out Categories');
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
