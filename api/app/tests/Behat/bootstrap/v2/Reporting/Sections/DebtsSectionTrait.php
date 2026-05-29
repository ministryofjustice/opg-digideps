<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\Reporting\Sections;

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
    public function iViewAndStartDebtsSection(): void
    {
        $this->iVisitDebtsSection();
        $this->pressButton('Start debts');
    }

    /**
     * @When I have no debts
     */
    public function iHaveNoDebts(): void
    {
        $this->iAmOnDebtsExistPage();
        $this->chooseOption('yes_no[hasDebts]', 'no', 'debts');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I have a debt to add
     */
    public function iHaveADebtToAdd(): void
    {
        $this->iAmOnDebtsExistPage();
        $this->chooseOption('yes_no[hasDebts]', 'yes', 'debts');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I add some debt values
     */
    public function iAddSomeDebtValues(): void
    {
        $this->addADebtsPayment('credit-cards', 1500);
        $this->addADebtsPayment('care-fees', 200);
        $this->addADebtsPayment('other', 700);
        $this->addADebtsPayment('loans', 10);

        $this->pressButton('Save and continue');
    }

    /**
     * @When I add an 'Other' debt but don't complete the more details field
     */
    public function iAddOtherDebtWithoutMoreDetails(): void
    {
        $this->addADebtsPayment('other', 700, fillMoreDetails: false);

        $this->pressButton('Save and continue');
    }

    /**
     * @When I say how the debts are being managed
     */
    public function iSayHowTheDebtsAreBeingManaged(): void
    {
        $this->iAmOnDebtsManagementPage();
        $this->fillInField('debtManagement[debtManagement]', 'Lorem ipsum', 'debts');
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see the expected debts section summary
     */
    public function iShouldSeeTheExpectedDebtsSummary(): void
    {
        $this->iAmOnDebtsSummaryPage();

        $this->expectedResultsDisplayedSimplified('debts', false, false);
    }

    /**
     * @When I edit an existing debt payment
     */
    public function iEditAnExistingDebtPayment(): void
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
    public function iAddADebtWithInvalidAmount(): void
    {
        $this->iHaveADebtToAdd();
        $this->addADebtsPayment('credit-cards', 0);
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see the validation message
     */
    public function iShouldSeeTheValidationMessage(): void
    {
        $this->assertOnAlertMessage('Enter at least one debt');
    }

    /**
     * @Then I should see 'Give us more information' error
     */
    public function iShouldSeeBlahBlahError(): void
    {
        $this->assertOnErrorMessage('Give us more information about this amount');
    }

    public function addADebtsPayment(string $type, int $amount, bool $fillMoreDetails = true): void
    {
        $fieldName = sprintf('debt[debts][%s][amount]', $this->debtTypeToFieldNameDictionary[$type]);
        $this->fillInFieldTrackTotal($fieldName, $amount, 'debts');

        if ($fillMoreDetails && $type === 'other') {
            $this->fillInField('debt[debts][3][moreDetails]', $this->faker->text(60), 'debts');
        }
    }
}
