<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\Reporting\Sections;

use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;

trait MoneyOutSectionTrait
{
    private array $paymentTypeDictionary = [
        'care-fees' => 'Care fees',
        'local-authority-charges-for-care' => 'Local authority charges for care',
        'medical-expenses' => 'Medical expenses',
        'medical-insurance' => 'Medical insurance',
        'broadband' => 'Broadband',
        'council-tax' => 'Council tax',
        'dual-fuel' => 'Dual fuel (Combined electricity & gas)',
        'electricity' => 'Electricity',
        'food' => 'Food',
        'gas' => 'Gas',
        'insurance-eg-life-home-contents' => 'Insurance (for example, life, home and contents)',
        'property-maintenance-improvement' => 'Property maintenance/improvement',
        'telephone' => 'Phone',
        'tv-services' => 'TV services',
        'water' => 'Water',
        'accommodation-service-charge' => 'Accommodation service charge',
        'mortgage' => 'Mortgage',
        'rent' => 'Rent',
        'client-transport-bus-train-taxi-fares' => 'Transport (for example, bus, train, taxi fares)',
        'clothes' => 'Clothes',
        'day-trips' => 'Day trips',
        'holidays' => 'Holidays',
        'personal-allowance-pocket-money' => 'Personal allowance or pocket money',
        'toiletries' => 'Toiletries',
        'deputy-security-bond' => 'Deputy security bond',
        'opg-fees' => 'OPG\'s fees',
        'professional-fees-eg-solicitor-accountant' => 'Fees charged by a solicitor, accountant or other professional',
        'professional-fees-eg-solicitor-accountant-non-lay' => 'Fees charged by a solicitor, accountant or other professional (Do not include deputy costs)',
        'investment-bonds-purchased' => 'Investment bond',
        'investment-account-purchased' => 'Investment fund account',
        'stocks-and-shares-purchased' => 'Stocks and shares',
        'purchase-over-1000' => 'Other purchase over £1,000',
        'bank-charges' => 'Bank charge',
        'credit-cards-charges' => 'Credit card charge',
        'loans' => 'Loan repayment',
        'tax-payments-to-hmrc' => 'Tax payment to HMRC',
        'unpaid-care-fees' => 'Unpaid care fee',
        'cash-withdrawn' => 'Cash withdrawn from %s\'s account',
        'transfers-out-to-other-accounts' => 'Transfers from %s\'s account to other accounts',
        'anything-else-paid-out' => 'Anything else paid out',
    ];

    private array $moneyTypeCategoriesCompleted = [];
    private array $moneyOutTransaction = [];

    #[When('I view and start the money out report section')]
    public function iViewAndStartMoneyOutSection(): void
    {
        $this->iVisitMoneyOutSection();
        $this->clickLink('Start money out');
    }

    #[Given('/^I confirm "([^"]*)" to taking money out on the clients behalf$/')]
    public function iConfirmToTakingMoneyOutOnTheClientsBehalf($arg1): void
    {
        $this->chooseOption('does_money_out_exist[moneyOutExists]', $arg1, 'moneyOutExists');
        $this->pressButton('Save and continue');
    }

    #[Then('/^I select from the money out payment options$/')]
    public function iSelectFromTheMoneyOutPaymentOptions(): void
    {
        $this->paymentTypeDictionary['cash-withdrawn'] = sprintf(
            $this->paymentTypeDictionary['cash-withdrawn'],
            $this->loggedInUserDetails->getClientFirstName()
        );

        $this->paymentTypeDictionary['transfers-out-to-other-accounts'] = sprintf(
            $this->paymentTypeDictionary['transfers-out-to-other-accounts'],
            $this->loggedInUserDetails->getClientFirstName()
        );
    }

    #[When('I try to save and continue without adding a payment')]
    public function iSaveAndContinueWithoutAddingPayment(): void
    {
        $this->iAmOnMoneyOutAddPaymentPage();
        $this->pressButton('Save and continue');
    }

    #[Then('I should see correct money out validation message')]
    public function iShouldSeeCorrectMoneyOutValidation(): void
    {
        $this->assertOnAlertMessage('Please choose an option');
    }

    #[When('I add one money out payment')]
    public function iAddOneMoneyOutPayment(): void
    {
        $this->iAmOnMoneyOutAddPaymentPage();
        $this->addPayment('care-fees', 'Care fees', false);
    }

    #[When('I add one type of money out payment from each category')]
    public function iAddOneTypeOfMoneyOutPaymentFromEachCategory(): void
    {
        $this->iAmOnMoneyOutAddPaymentPage();

        $this->addPayment('care-fees', $this->paymentTypeDictionary['care-fees'], true);
        $this->addPayment('property-maintenance-improvement', $this->paymentTypeDictionary['property-maintenance-improvement'], true);
        $this->addPayment('mortgage', $this->paymentTypeDictionary['mortgage'], true);
        $this->addPayment('personal-allowance-pocket-money', $this->paymentTypeDictionary['personal-allowance-pocket-money'], true);
        $this->addPayment('opg-fees', $this->paymentTypeDictionary['opg-fees'], true);
        $this->addPayment('purchase-over-1000', $this->paymentTypeDictionary['purchase-over-1000'], true);
        $this->addPayment('unpaid-care-fees', $this->paymentTypeDictionary['unpaid-care-fees'], true);
        $this->addPayment('cash-withdrawn', $this->paymentTypeDictionary['cash-withdrawn'], true);
        $this->addPayment('anything-else-paid-out', $this->paymentTypeDictionary['anything-else-paid-out'], false);
    }

    #[When('I remove an existing money out payment')]
    public function iRemoveAnExistingMoneyOutPayment(): void
    {
        $this->iAmOnMoneyOutSummaryPage();

        $this->removeAnswerFromSection('account[amount]', 'addPayment-Care fees', true, 'Yes, remove payment');
    }

    #[When('I edit an existing money out payment')]
    public function iEditExistingMoneyOutPayment(): void
    {
        $this->iAmOnMoneyOutSummaryPage();

        $this->getSession()->getPage()->find('xpath', '//td[contains(., "Care fees")]/..')->clickLink('Edit');

        $this->assertPageContainsText('Edit a payment: Care fees');

        $this->fillInPaymentDetails('Care fees', $this->faker->sentence(rand(5, 50)), mt_rand(1, 999));
    }

    #[When('I add another money out payment from an existing account')]
    public function iAddAnotherMoneyOutPaymentExistingAccount(): void
    {
        $this->iAmOnMoneyOutSummaryPage();

        $this->clickLink('Add a payment');

        $this->iAmOnMoneyOutAddPaymentPage();

        $translatedPaymentValue = 'Water';
        $radioPaymentValue = array_search($translatedPaymentValue, $this->paymentTypeDictionary);

        $this->addPayment($radioPaymentValue, $translatedPaymentValue, false);
    }

    #[When('I add a payment without filling in description and amount')]
    public function iAddPaymentWithoutFillingInDescriptionAndAmount(?bool $addAnother = null): void
    {
        $this->chooseOption('account[category]', 'purchase-over-1000', 'addPayment');
        $this->pressButton('Save and continue');
        $this->fillInPaymentDetails('Other purchase over £1,000', null, null, $addAnother);
    }

    #[When('I should see correct money out description and amount validation message')]
    public function iSeeMoneyOutDescriptionValidationMessage(): void
    {
        $this->assertOnAlertMessage('Please give us some more information about this amount');
        $this->assertOnAlertMessage('Please enter an amount');
    }

    #[Then('I should see the expected results on money out summary page')]
    public function iShouldSeeExpectedResultsOnMoneyOutSummaryPage(): void
    {
        $this->iAmOnMoneyOutSummaryPage();

        if ($this->getSectionAnswers('moneyOutExists')) {
            $this->expectedResultsDisplayedSimplified('moneyOutExists');
        }

        if ($this->getSectionAnswers('reasonForNoMoneyOut')) {
            $this->expectedResultsDisplayedSimplified('reasonForNoMoneyOut');
        }

        foreach (array_unique($this->moneyTypeCategoriesCompleted) as $completedCategory) {
            $this->expectedResultsDisplayedSimplified($completedCategory);
        }
    }

    private function addPayment(string $radioPaymentValue, string $translatedPaymentValue, ?bool $addAnother = null): void
    {
        $this->chooseOption('account[category]', $radioPaymentValue, 'addPayment-' . $translatedPaymentValue, $translatedPaymentValue);
        $this->pressButton('Save and continue');

        $paymentAmount = mt_rand(0, 999);
        $this->fillInPaymentDetails($translatedPaymentValue, $this->faker->sentence(rand(5, 50)), $paymentAmount, $addAnother);

        $this->moneyOutTransaction[] = [$this->paymentTypeDictionary['professional-fees-eg-solicitor-accountant'] => $paymentAmount];
    }

    private function fillInPaymentDetails(string $translatedPaymentValue, ?string $paymentDescription = null, ?int $paymentAmount = null, ?bool $addAnother = null): void
    {
        $this->iAmOnMoneyOutAddPaymentDetailsPage();

        if ($paymentDescription) {
            $this->fillInField('account[description]', $paymentDescription, 'addPayment-' . $translatedPaymentValue);
        }

        if ($paymentAmount) {
            $this->fillInFieldTrackTotal('account[amount]', $paymentAmount, 'addPayment-' . $translatedPaymentValue);
        }

        if ('Care fees' === $translatedPaymentValue) {
            $this->chooseOption(
                'account[bankAccountId]',
                $this->loggedInUserDetails->getCurrentReportBankAccountId(),
                'addPayment-' . $translatedPaymentValue,
                '(****1234)'
            );
        }

        if ($addAnother !== null) {
            $this->selectOption('account[addAnother]', $addAnother ? 'yes' : 'no');
        }

        $this->pressButton('Save and continue');
    }

    #[When('I add the Fees charged by a solicitor, accountant or other professional payment')]
    public function iAddTheFeesChargedByASolicitorAccountantOrOtherProfessionalPayment(): void
    {
        $this->iAmOnMoneyOutAddPaymentPage();

        $this->addPayment('professional-fees-eg-solicitor-accountant', $this->paymentTypeDictionary['professional-fees-eg-solicitor-accountant'], false);
    }

    #[When('I add the Fees charged by a solicitor, accountant or other professional payment not including deputy costs')]
    public function iAddTheFeesChargedByASolicitorAccountantOrOtherProfessionalPaymentNotIncludingDeputyCosts(): void
    {
        $this->iAmOnMoneyOutAddPaymentPage();

        $this->addPayment('professional-fees-eg-solicitor-accountant-non-lay', $this->paymentTypeDictionary['professional-fees-eg-solicitor-accountant-non-lay'], false);
    }

    #[Then('/^I enter a reason for no money out$/')]
    public function iEnterAReasonForNoMoneyOut(): void
    {
        $this->iAmOnNoMoneyOutExistsPage();

        $this->fillInField('reason_for_no_money[reasonForNoMoneyOut]', 'No money out', 'reasonForNoMoneyOut');
        $this->pressButton('Save and continue');
    }

    #[Then('/^the money out summary page should contain "([^"]*)" money in values$/')]
    public function theMoneyOutSummaryPageShouldContainMoneyInValues($arg1): void
    {
        $this->iAmOnMoneyOutSummaryPage();

        $transactionItemTableRows = $this->getSession()->getPage()->find('xpath', "//tr[contains(@class,'behat-region-transaction-')]");

        if ('no' == $arg1) {
            $this->assertIsNull($transactionItemTableRows, 'Transaction items are not rendered');

            $this->expectedResultsDisplayedSimplified(null, true, false, false, false);
        } else {
            $this->assertPageContainsText('Payments you\'ve already told us about');

            foreach ($this->moneyOutTransaction as $transactionItems) {
                foreach ($transactionItems as $moneyType => $value) {
                    $this->assertElementContainsText('main', $moneyType);
                    $this->assertElementContainsText('main', '£' . number_format($value, 2));
                }
            }
            $this->expectedResultsDisplayedSimplified();
        }
    }

    #[When('/^I edit the money out exist summary section$/')]
    public function iEditTheMoneyOutExistSummarySection(): void
    {
        $this->iAmOnMoneyOutSummaryPage();

        // clean data to correctly track expected results when user edits answers.
        $this->removeSection('moneyOutExists');
        $this->removeSection('addPayment-' . $this->paymentTypeDictionary['professional-fees-eg-solicitor-accountant']);
        $this->removeSection('reasonForNoMoneyOut');

        $urlRegex = sprintf('/%s\/.*\/money-out\/exist\?from\=summary$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
    }

    #[When('/^I delete the money out transaction item from the summary page$/')]
    public function iDeleteTheMoneyOutTransactionItemFromTheSummaryPage(): void
    {
        $this->iAmOnMoneyOutSummaryPage();

        $formSectionName = 'addPayment-' . $this->paymentTypeDictionary['professional-fees-eg-solicitor-accountant'];

        $this->removeAnswerFromSection(
            'account[category]',
            $formSectionName,
            true,
            'Yes, remove payment'
        );

        foreach ($this->moneyOutTransaction[0] as $value) {
            $this->subtractFromGrandTotal($value);
        }

        $this->moneyOutTransaction = [];
    }

    #[When('/^I add a new money out payment$/')]
    public function iAddANewMoneyOutPayment(): void
    {
        $this->clickLink('Add a payment');

        $this->iSelectFromTheMoneyOutPaymentOptions();
        $this->iAddTheFeesChargedByASolicitorAccountantOrOtherProfessionalPayment();
    }
}
