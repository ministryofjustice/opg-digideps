<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait DebtsSectionTrait
{
    private bool $hasDebts = false;
    private array $debtLists = [];

    /**
     * @When I view and start the debts report section
     */
    public function iViewAndStartDebtsSection()
    {
        $this->iVisitDebtsSection();
        $this->clickLink('Starts debts');
    }

    /**
     * @When I have no debts
     */
    public function iHaveNoDebts()
    {
        $this->iAmOnDebtsExistPage();
        $this->selectOption('yes_no[hasDebts]', 'no');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I have a debt to add
     */
    public function iAddADebt()
    {
        $this->iAmOnDebtsExistsPage();
        $this->selectOption('yes_no[hasDebts]', 'yes');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I add some debt values
     */
    public function iAddSomeDebtValues()
    {
        $this->hasDebts = true;
        $this->debtLists[] = 'Credit cards';
        $this->fillField('debt[debts][1][amount]', '1500');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I say how the debts are being managed
     */
    public function iSayHowTheDebtsAreBeingManaged()
    {
        $this->iAmOnDebtsManagementPage();
        $this->fillField('debtManagement[debtManagement]', 'Lorem ipsum');
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see the expected debts section summary
     */
    public function iShouldSeeTheExpectedDebtsSummary()
    {
        $this->iAmOnDebtsSummaryPage();
        $descriptionListItems = $this->findAllCssElements('dl');
        $debts = $descriptionListItems[0];
        $this->checkDebts($debts);
    }

    private function checkDebts($debts)
    {
        $debtsRows = $debts->findAll('css', 'div.govuk-summary-list__row');

        if (!$debtsRows) {
            $this->throwContextualException('A div element was not found on the page');
        }

        if (!$this->hasDebts) {
            $this->assertStringContainsString('No', $debtsRows[1]->getHtml(), 'Debts list');
        } else {
            $debtsListItems = $debtsRows[1]->findAll('css', 'li');
            foreach ($this->debtLists as $expectedDebtKey => $expectedDebt) {
                $this->assertStringContainsString($expectedDebt, $debtsListItems[$expectedDebtKey]->getHtml(), 'Debts list');
            }
        }
    }
}
