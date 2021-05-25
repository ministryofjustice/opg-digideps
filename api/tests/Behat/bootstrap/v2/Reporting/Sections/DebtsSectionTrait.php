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
        $this->pressButton('Start debts');
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
        $this->iAmOnDebtsExistPage();
        $this->selectOption('yes_no[hasDebts]', 'yes');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I add some debt values
     */
    public function iAddSomeDebtValues()
    {
        $this->hasDebts = true;
        $this->debtLists[] = ['Credit cards'];
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

        if (!$this->hasDebts) {
            $haveDebts[] = ['no'];
        } else {
            $haveDebts[] = ['yes'];
        }

        $this->expectedResultsDisplayed(0, $haveDebts, 'Answer for "Does user have any debts?"');
    }
}
