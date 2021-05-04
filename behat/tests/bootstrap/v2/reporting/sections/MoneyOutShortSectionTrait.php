<?php

declare(strict_types=1);

namespace DigidepsBehat\v2\Reporting\Sections;

trait MoneyOutShortSectionTrait
{
    private array $moneyOutList = [];
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
     * @Then I should see the expected money out section summary
     */
    public function iShouldSeeTheExpectedMoneyOutSummary()
    {
        $this->iAmOnMoneyOutShortSummaryPage();

        $tableBodies = $this->getSession()->getPage()->findAll('css', 'dl');

        if (!$tableBodies) {
            $this->throwContextualException('A dl element was not found on the page');
        }

        foreach ($tableBodies as $tRowKey => $tableRow) {
            $tableRows = $tableBody->findAll('css', 'div.govuk-summary-list__row');

            if (!$tableRows) {
                $this->throwContextualException('A div element was not found on the page');
            }
            //first row is the header so get second
            var_dump($tableRows[0]->getHtml());
            var_dump($tableRows[1]->getHtml());
        }
//        foreach ($tableRows as $tRowKey=>$tableRow) {
//            $tableHeader = $tableRow->find('css', $accountSummaryElems['head']);
//            $headHtml = trim(strtolower($tableHeader->getHtml()));
//            $this->assertStringContainsString($this->accountList[$tRowKey]['accountType'], $headHtml, 'Accounts Type');
//            $this->assertStringContainsString($this->accountList[$tRowKey]['name'], $headHtml, 'Accounts Name');
//            $this->assertStringContainsString($this->accountList[$tRowKey]['accountNumber'], $headHtml, 'Accounts Number');
//
//            $sortCode = str_replace('-', '', $this->accountList[$tRowKey]['sortCode']);
//            $this->assertStringContainsString($sortCode, $headHtml, 'Accounts Sort Code');
//            $this->assertStringContainsString($this->accountList[$tRowKey]['joint'], $headHtml, 'Accounts Joint');
//
//            $tableFields = $tableRow->findAll('css', $accountSummaryElems['data']);
//
//            foreach ($tableFields as $tFieldKey=>$tableField) {
//                $balanceItem = trim(strtolower($tableField->getHtml()));
//                if ($tFieldKey == 0) {
//                    $this->assertStringContainsString($this->accountList[$tRowKey]['openingBalance'], $balanceItem, 'Accounts Opening Balance');
//                } elseif ($tFieldKey == 1 and $this->reportUrlPrefix != 'ndr') {
//                    $this->assertStringContainsString($this->accountList[$tRowKey]['closingBalance'], $balanceItem, 'Accounts Closing Balance');
//                }
//            }
//        }
    }
}
