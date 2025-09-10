<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait DebtsSectionTrait
{
    private array $debtTypeToFieldNameDictionary = [
        'care-fees' => 0,
        'credit-cards' => 1,
        'loans' => 2,
        'other' => 3,
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
        $this->chooseOption('yes_no[hasDebts]', 'no', 'debts');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I have a debt to add
     */
    public function iHaveADebtToAdd()
    {
        $this->iAmOnDebtsExistPage();
        $this->chooseOption('yes_no[hasDebts]', 'yes', 'debts');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I add some debt values
     */
    public function iAddSomeDebtValues()
    {
        $this->addADebtsPayment('credit-cards', 1500);
        $this->addADebtsPayment('care-fees', 200);
        $this->addADebtsPayment('other', 700);
        $this->addADebtsPayment('loans', 10);

        $this->pressButton('Save and continue');
    }

    /**
     * @When I say how the debts are being managed
     */
    public function iSayHowTheDebtsAreBeingManaged()
    {
        $this->iAmOnDebtsManagementPage();
        $this->fillInField('debtManagement[debtManagement]', 'Lorem ipsum', 'debts');
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see the expected debts section summary
     */
    public function iShouldSeeTheExpectedDebtsSummary()
    {
        $this->iAmOnDebtsSummaryPage();

        $this->expectedResultsDisplayedSimplified('debts', false, false);
    }

    /**
     * @When I edit an existing debt payment
     */
    public function iEditAnExistingDebtPayment()
    {
        $this->iVisitDebtsSummarySection();
        $this->iAmOnDebtsSummaryPage();

        $locator = '//div[normalize-space()="List of debts"]/..';
        $debtsListDiv = $this->getSession()->getPage()->find('xpath', $locator);

        $debtsListDiv->clickLink('Edit');

        $newValue = $this->faker->numberBetween(1, 10000);

        $this->fillInFieldTrackTotal(
            'debt[debts][0][amount]',
            $newValue,
            'debts'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I add a debt with invalid amount
     */
    public function iAddADebtWithInvalidAmount()
    {
        $this->iHaveADebtToAdd();
        $this->addADebtsPayment('credit-cards', 0);
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see the validation message
     */
    public function iShouldSeeTheValidationMessage()
    {
        $this->assertOnAlertMessage('Enter at least one debt');
    }

    public function addADebtsPayment(string $type, int $amount)
    {
        $fieldName = sprintf('debt[debts][%s][amount]', $this->debtTypeToFieldNameDictionary[$type]);
        $this->fillInFieldTrackTotal($fieldName, $amount, 'debts');

        if ('other' === $type) {
            $this->fillInField('debt[debts][3][moreDetails]', $this->faker->text(60), 'debts');
        }
    }
}
