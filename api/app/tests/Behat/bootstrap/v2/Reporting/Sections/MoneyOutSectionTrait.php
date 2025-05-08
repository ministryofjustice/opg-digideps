<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

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

    /**
     * @When I view and start the money out report section
     */
    public function iViewAndStartMoneyOutSection()
    {
        $this->iVisitMoneyOutSection();
        $this->clickLink('Start money out');
    }

    /**
     * @Given /^I confirm "([^"]*)" to taking money out on the clients behalf$/
     */
    public function iConfirmToTakingMoneyOutOnTheClientsBehalf($arg1)
    {
        $this->chooseOption('does_money_out_exist[moneyOutExists]', $arg1, 'moneyOutExists');
        $this->pressButton('Save and continue');
    }

    /**
     * @Then /^I select from the money out payment options$/
     */
    public function iSelectFromTheMoneyOutPaymentOptions()
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

    /**
     * @When I try to save and continue without adding a payment
     */
    public function iSaveAndContinueWithoutAddingPayment()
    {
        $this->iAmOnMoneyOutAddPaymentPage();
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see correct money out validation message
     */
    public function iShouldSeeCorrectMoneyOutValidation()
    {
        $this->assertOnAlertMessage('Please choose an option');
    }

    /**
     * @When I add one money out payment
     */
    public function iAddOneMoneyOutPayment()
    {
        $this->iAmOnMoneyOutAddPaymentPage();
        $this->addPayment('care-fees', 'Care fees');
        $this->addAnother('no');
    }

    /**
     * @When I add one type of money out payment from each category
     */
    public function iAddOneTypeOfMoneyOutPaymentFromEachCategory()
    {
        $this->iAmOnMoneyOutAddPaymentPage();

        $this->addPayment('care-fees', $this->paymentTypeDictionary['care-fees']);
        $this->addAnother('yes');
        $this->addPayment('property-maintenance-improvement', $this->paymentTypeDictionary['property-maintenance-improvement']);
        $this->addAnother('yes');
        $this->addPayment('mortgage', $this->paymentTypeDictionary['mortgage']);
        $this->addAnother('yes');
        $this->addPayment('personal-allowance-pocket-money', $this->paymentTypeDictionary['personal-allowance-pocket-money']);
        $this->addAnother('yes');
        $this->addPayment('opg-fees', $this->paymentTypeDictionary['opg-fees']);
        $this->addAnother('yes');
        $this->addPayment('purchase-over-1000', $this->paymentTypeDictionary['purchase-over-1000']);
        $this->addAnother('yes');
        $this->addPayment('unpaid-care-fees', $this->paymentTypeDictionary['unpaid-care-fees']);
        $this->addAnother('yes');
        $this->addPayment('cash-withdrawn', $this->paymentTypeDictionary['cash-withdrawn']);
        $this->addAnother('yes');
        $this->addPayment('anything-else-paid-out', $this->paymentTypeDictionary['anything-else-paid-out']);
        $this->addAnother('no');
    }

    /**
     * @When I remove an existing money out payment
     */
    public function iRemoveAnExistingMoneyOutPayment()
    {
        $this->iAmOnMoneyOutSummaryPage();

        $this->removeAnswerFromSection('account[amount]', 'addPayment-Care fees', true, 'Yes, remove payment');
    }

    /**
     * @When I edit an existing money out payment
     */
    public function iEditExistingMoneyOutPayment()
    {
        $this->iAmOnMoneyOutSummaryPage();

        $this->getSession()->getPage()->find('xpath', '//td[contains(., "Care fees")]/..')->clickLink('Edit');

        $this->fillInPaymentDetails('Care fees', $this->faker->sentence(rand(5, 50)), mt_rand(1, 999));
    }

    /**
     * @When I add another money out payment from an existing account
     */
    public function iAddAnotherMoneyOutPaymentExistingAccount()
    {
        $this->iAmOnMoneyOutSummaryPage();

        $this->clickLink('Add a payment');

        $this->iAmOnMoneyOutAddPaymentPage();

        $translatedPaymentValue = 'Water';
        $radioPaymentValue = array_search($translatedPaymentValue, $this->paymentTypeDictionary);

        $this->addPayment($radioPaymentValue, $translatedPaymentValue);

        $this->addAnother('no');
    }

    /**
     * @When I add a payment without filling in description and amount
     */
    public function iAddPaymentWithoutFillingInDescriptionAndAmount()
    {
        $this->chooseOption('account[category]', 'purchase-over-1000', 'addPayment');
        $this->pressButton('Save and continue');
        $this->fillInPaymentDetails('Other purchase over £1,000', null, null);
    }

    /**
     * @When I should see correct money out description and amount validation message
     */
    public function iSeeMoneyOutDescriptionValidationMessage()
    {
        $this->assertOnAlertMessage('Please give us some more information about this amount');
        $this->assertOnAlertMessage('Please enter an amount');
    }

    /**
     * @Then I should see the expected results on money out summary page
     */
    public function iShouldSeeExpectedResultsOnMoneyOutSummaryPage()
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

    private function addPayment(string $radioPaymentValue, string $translatedPaymentValue)
    {
        $this->chooseOption('account[category]', $radioPaymentValue, 'addPayment-'.$translatedPaymentValue, $translatedPaymentValue);
        $this->pressButton('Save and continue');

        $paymentAmount = mt_rand(0, 999);
        $this->fillInPaymentDetails($translatedPaymentValue, $this->faker->sentence(rand(5, 50)), $paymentAmount);

        $this->moneyOutTransaction[] = [$this->paymentTypeDictionary['professional-fees-eg-solicitor-accountant'] => $paymentAmount];
    }

    private function fillInPaymentDetails(string $translatedPaymentValue, ?string $paymentDescription = null, ?int $paymentAmount = null)
    {
        $this->iAmOnMoneyOutAddPaymentDetailsPage();

        if ($paymentDescription) {
            $this->fillInField('account[description]', $paymentDescription, 'addPayment-'.$translatedPaymentValue);
        }

        if ($paymentAmount) {
            $this->fillInFieldTrackTotal('account[amount]', $paymentAmount, 'addPayment-'.$translatedPaymentValue);
        }

        if ('Care fees' === $translatedPaymentValue) {
            $this->chooseOption(
                'account[bankAccountId]',
                $this->loggedInUserDetails->getCurrentReportBankAccountId(),
                'addPayment-'.$translatedPaymentValue,
                '(****1234)'
            );
        }

        $this->pressButton('Save and continue');
    }

    private function addAnother($anotherFlag)
    {
        $this->iAmOnMoneyOutAddAnotherPaymentPage();

        $this->chooseOption('add_another[addAnother]', $anotherFlag);
        $this->pressButton('Save and continue');
    }

    /**
     * @When I add the Fees charged by a solicitor, accountant or other professional payment
     */
    public function iAddTheFeesChargedByASolicitorAccountantOrOtherProfessionalPayment()
    {
        $this->iAmOnMoneyOutAddPaymentPage();

        $this->addPayment('professional-fees-eg-solicitor-accountant', $this->paymentTypeDictionary['professional-fees-eg-solicitor-accountant']);
        $this->addAnother('no');
    }

    /**
     * @When I add the Fees charged by a solicitor, accountant or other professional payment not including deputy costs
     */
    public function iAddTheFeesChargedByASolicitorAccountantOrOtherProfessionalPaymentNotIncludingDeputyCosts()
    {
        $this->iAmOnMoneyOutAddPaymentPage();

        $this->addPayment('professional-fees-eg-solicitor-accountant-non-lay', $this->paymentTypeDictionary['professional-fees-eg-solicitor-accountant-non-lay']);
        $this->addAnother('no');
    }

    /**
     * @Then /^I enter a reason for no money out$/
     */
    public function iEnterAReasonForNoMoneyOut()
    {
        $this->iAmOnNoMoneyOutExistsPage();

        $this->fillInField('reason_for_no_money[reasonForNoMoneyOut]', 'No money out', 'reasonForNoMoneyOut');
        $this->pressButton('Save and continue');
    }

    /**
     * @Then /^the money out summary page should contain "([^"]*)" money in values$/
     */
    public function theMoneyOutSummaryPageShouldContainMoneyInValues($arg1)
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
                    $this->assertElementContainsText('main', '£'.number_format($value, 2));
                }
            }
            $this->expectedResultsDisplayedSimplified();
        }
    }

    /**
     * @When /^I edit the money out exist summary section$/
     */
    public function iEditTheMoneyOutExistSummarySection()
    {
        $this->iAmOnMoneyOutSummaryPage();

        // clean data to correctly track expected results when user edits answers.
        $this->removeSection('moneyOutExists');
        $this->removeSection('addPayment-'.$this->paymentTypeDictionary['professional-fees-eg-solicitor-accountant']);
        $this->removeSection('reasonForNoMoneyOut');

        $urlRegex = sprintf('/%s\/.*\/money-out\/exist\?from\=summary$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
    }

    /**
     * @When /^I delete the money out transaction item from the summary page$/
     */
    public function iDeleteTheMoneyOutTransactionItemFromTheSummaryPage()
    {
        $this->iAmOnMoneyOutSummaryPage();

        $formSectionName = 'addPayment-'.$this->paymentTypeDictionary['professional-fees-eg-solicitor-accountant'];

        $this->removeAnswerFromSection(
            'account[category]',
            $formSectionName,
            true,
            'Yes, remove payment'
        );

        foreach ($this->moneyOutTransaction[0] as $moneyType => $value) {
            $this->subtractFromGrandTotal($value);
        }

        $this->moneyOutTransaction = [];
    }

    /**
     * @When /^I add a new money out payment$/
     */
    public function iAddANewMoneyOutPayment()
    {
        $this->clickLink('Add a payment');

        $this->iSelectFromTheMoneyOutPaymentOptions();
        $this->iAddTheFeesChargedByASolicitorAccountantOrOtherProfessionalPayment();
    }
}
