<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait DebtsSectionTrait
{
    private bool $hasDebts = false;
    private array $debtManagement = [];
    private array $debtList = [];
    private float $totalAmount = 0.00;

    /**
     * The order of these has to corresponds to the order
     * of the inputs on the debts page when adding a debt
     * otherwise the addADebtsPayment() function will not
     * function correctly.
     */
    private array $debtTypes = [
        'care-fees' => [
            'name' => 'Outstanding care home fees',
            'amount' => '£0.00',
        ],
        'credit-cards' => [
            'name' => 'Credit cards',
            'amount' => '£0.00',
        ],
        'loans' => [
            'name' => 'Loans',
            'amount' => '£0.00',
        ],
        'other' => [
            'name' => 'Other',
            'amount' => '£0.00',
        ],
        'total-amount' => [
            'name' => 'Total amount',
            'amount' => '£0.00',
        ],
    ];

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
        $this->addADebtsPayment('credit-cards', '1500');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I say how the debts are being managed
     */
    public function iSayHowTheDebtsAreBeingManaged()
    {
        $this->iAmOnDebtsManagementPage();
        $this->debtManagement[] = ['Lorem ipsum'];
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

        if ($this->hasDebts) {
            // Convert the debts list amount into the correct format for comparison
            $expectedDebtsList = [];
            foreach ($this->debtTypes as $debtListKey => $debtList) {
                /*
                 * The total amount part of the debtTypes will have a small
                 * difference when formatting the amount as it $this->totalAmount variable
                 * is a float by default so we will have to cast it as a string before passing it
                 * to the moneyFormat() function.
                 */
                foreach ($debtList as $debtItemKey => $debtItem) {
                    if ('amount' === $debtItemKey && 'total-amount' === $debtListKey) {
                        $debtList['amount'] = '£'.$this->moneyFormat((string) $this->totalAmount);
                    } elseif ('amount' === $debtItem) {
                        $debtList['amount'] = '£'.$this->moneyFormat($debtItem);
                    }
                }

                $expectedDebtsList[] = [$debtList['name'], $debtList['amount']];
            }

            $expectedDebtsList = array_values($expectedDebtsList);
            $this->expectedResultsDisplayed(1, $expectedDebtsList, 'List of debts and their amount');
            $this->expectedResultsDisplayed(2, $this->debtManagement, 'Answer for "How is the debt being managed or reduced?"');
        }
    }

    public function addADebtsPayment(string $type, string $amount)
    {
        $this->debtTypes[$type]['amount'] = $amount;
        // $amount is passed in as a string so we need to cast it as a float here
        $this->totalAmount += (float) $amount;

        /**
         * the index of the debtType lines up with the
         * input index on the debts page.
         */
        $typeIndex = array_search($type, array_keys($this->debtTypes));
        $this->fillField("debt[debts][{$typeIndex}][amount]", $amount);
    }
}
