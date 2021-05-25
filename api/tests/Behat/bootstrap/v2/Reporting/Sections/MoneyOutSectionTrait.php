<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait MoneyOutSectionTrait
{
    // this page has a special sub section layout which means we need to nest one more level than usual
    private array $paymentsList = [];
    private array $paymentsListForFills = [];
    private int $totalMoneyOut = 0;

    /**
     * @When I view and start the money out report section
     */
    public function iViewAndStartMoneyOutSection()
    {
        $this->iVisitMoneyOutSection();
        $this->clickLink('Start money out');
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
     * @When I add one of each type of money out payment
     */
    public function iAddOneOfEachTypeOfMoneyOutPayment()
    {
        $this->iAmOnMoneyOutAddPaymentPage();

        $xpath = "//form[@name='account']//fieldset";
        $fieldSets = $this->getSession()->getPage()->findAll('xpath', $xpath);

        foreach ($fieldSets as $fieldSetKey => $fieldset) {
            $total = 0;
            $xpath = "//div[contains(@class, 'govuk-radios__item')]";
            $divs = $fieldset->findAll('xpath', $xpath);

            foreach ($divs as $divKey => $div) {
                $xpath = "//label[contains(@class, 'govuk-radios__label')]";
                $label = $div->find('xpath', $xpath);
                $xpath = "//input[contains(@name, 'account[category]')]";
                $radioBox = $div->find('xpath', $xpath);

                $amount = (1000 + $divKey);

                $paymentObject =
                    [
                        'paymentName' => trim($label->getText()),
                        'description' => $this->faker->text(100),
                        'amount' => strval($amount),
                        'selectValue' => $this->getStringBetween($radioBox->getOuterHtml(), 'value="', '"'),
                    ];
                $this->paymentsListForFills[] = $paymentObject;

                // because each section has sub total the element is actually element * 2
                $this->paymentsList[$fieldSetKey * 2][] = $this->formatPaymentObject($paymentObject);

                $total += $amount;
            }

            // this references the sub total section for each sub section
            $this->paymentsList[$fieldSetKey * 2 + 1][] =
                [
                    'label' => 'total amount',
                    'total' => $this->moneyFormat($total),
                ];
        }

        foreach ($this->paymentsListForFills as $paymentKey => $payment) {
            if ($paymentKey >= (count($this->paymentsListForFills) - 1)) {
                $this->addPayment($payment, 'no');
            } else {
                $this->addPayment($payment, 'yes');
            }
        }
    }

    /**
     * @When I remove an existing money out payment
     */
    public function iRemoveAnExistingMoneyOutPayment()
    {
        $this->iAmOnMoneyOutSummaryPage();
        $this->setPaymentListToMoneyOutCompleteDefault();
        $this->removeMoneyOutPayment(2, 0, 1);
    }

    /**
     * @When I edit an existing money out payment
     */
    public function iEditExistingMoneyOutPayment()
    {
        $this->iAmOnMoneyOutSummaryPage();
        $this->setPaymentListToMoneyOutCompleteDefault();

        $urlRegex = sprintf('/%s\/.*\/money-out\/step2\/.*$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 1);

        $newAmount = 2000;

        $this->paymentsList[2][0]['description'] = $this->faker->text(100);
        $this->totalMoneyOut = $this->totalMoneyOut - intval($this->paymentsList[2][0]['amount']) + $newAmount;
        $this->paymentsList[3][0]['amount'] = strval(intval($this->paymentsList[3][0]['amount']) - intval($this->paymentsList[2][0]['amount']) + $newAmount);
        $this->paymentsList[2][0]['amount'] = strval($newAmount);

        $this->fillInPaymentDetails($this->paymentsList[2][0]);

        $this->paymentsList[0][0] = $this->formatPaymentObject($this->paymentsList[0][0]);
        $this->paymentsList[1][0]['amount'] = $this->moneyFormat($this->paymentsList[1][0]['amount']);
        $this->paymentsList[2][0] = $this->formatPaymentObject($this->paymentsList[2][0]);
        $this->paymentsList[3][0]['amount'] = $this->moneyFormat($this->paymentsList[3][0]['amount']);
    }

    /**
     * @When I add another money out payment from an existing account
     */
    public function iAddAnotherMoneyOutPaymentExistingAccount()
    {
        $this->iAmOnMoneyOutSummaryPage();
        $this->setPaymentListToMoneyOutCompleteDefault();

        $urlRegex = sprintf('/%s\/.*\/money-out\/step1.*$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
        $this->iAmOnMoneyOutAddPaymentPage();

        $newPaymentAmount = 250;

        // test account works as well as we don't have this on iAddOneOfEachTypeOfMoneyOutPayment section
        $paymentObject =
            [
                'paymentName' => 'care fees',
                'description' => $this->faker->text(100),
                'account' => '(****)',
                'amount' => strval($newPaymentAmount),
                'selectValue' => 'care-fees',
            ];

        $this->addPayment($paymentObject, 'no');
        // as this is care fees it goes in same section as previous care fees
        $this->paymentsList[0][] = $this->formatPaymentObject($paymentObject);
        $this->paymentsList[1][0]['amount'] = $this->moneyFormat(intval($this->paymentsList[1][0]['amount']) + $newPaymentAmount);
        $this->totalMoneyOut += $newPaymentAmount;
    }

    /**
     * @When I add a payment without filling in description
     */
    public function iAddPaymentWithoutFillingInDescription()
    {
        $this->selectOption('account[category]', 'purchase-over-1000');
        $this->pressButton('Save and continue');
        $payment = ['amount' => '200', 'description' => ''];
        $this->fillInPaymentDetails($payment);
    }

    /**
     * @When I should see correct money out description validation message
     */
    public function iSeeMoneyOutDescriptionValidationMessage()
    {
        $this->assertOnAlertMessage('Please give us some more information about this amount');
    }

    /**
     * @When I add a payment without filling in amount
     */
    public function iAddPaymentWithoutFillingInAmount()
    {
        $payment = ['amount' => '', 'description' => 'some text'];
        $this->fillInPaymentDetails($payment);
    }

    /**
     * @When I should see correct money out amount validation message
     */
    public function iSeeMoneyOutAmountValidationMessage()
    {
        $this->assertOnAlertMessage('Please enter an amount');
    }

    /**
     * @When I should see the expected results on money out summary page
     */
    public function iShouldSeeExpectedResultsOnMoneyOutPage()
    {
        $this->iAmOnMoneyOutSummaryPage();

        foreach ($this->paymentsList as $entryKey => $entry) {
            $this->expectedResultsDisplayed($entryKey, $this->paymentsList[$entryKey], 'Money Out Payments', true);
        }

        $this->checkTotalAmountOnSummary();
    }

    private function checkTotalAmountOnSummary()
    {
        $divs = $this->getSession()->getPage()->findAll('xpath', '//div');
        $total = strval($this->moneyFormat($this->totalMoneyOut));
        $totalExists = false;
        foreach ($divs as $div) {
            if (str_contains($div->getText(), 'Total money out')) {
                if (str_contains($div->getText(), $total)) {
                    $totalExists = true;
                }
            }
        }

        if (!$totalExists) {
            $this->throwContextualException(sprintf('total amount of %s not found on page', $total));
        }
    }

    private function setPaymentListToMoneyOutCompleteDefault()
    {
        // starting payments for a fixture of completed report
        $this->paymentsList = [
            [[
                'paymentName' => 'care fees',
                'description' => '',
                'amount' => '200',
            ]],
            [[
                'amount' => '200',
            ]],
            [[
                'paymentName' => 'electricity',
                'description' => '',
                'amount' => '100',
            ]],
            [[
                'amount' => '100',
            ]],
        ];

        $this->totalMoneyOut = 300;
    }

    // due to sub groups the occurrence on screen can be different to how we have to manipulate the paymentsList entry
    private function removeMoneyOutPayment($paymentSectionNumber, $paymentNumber, $occurenceOnSummary)
    {
        $urlRegex = sprintf('/%s\/.*\/money-out\/.*\/delete.*$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, $occurenceOnSummary);
        $this->iAmOnMoneyOutDeletePage();
        $this->iClickBasedOnAttributeTypeAndValue('button', 'id', 'confirm_delete_confirm');

        $this->removeFromList($paymentSectionNumber, $paymentNumber);
    }

    private function removeFromList($paymentSectionNumber, $paymentNumber)
    {
        $amountToRemove = intval($this->paymentsList[$paymentSectionNumber][$paymentNumber]['amount']);
        $sectionTotal = intval($this->paymentsList[$paymentSectionNumber + 1][0]['amount']);

        // this is the sub total section for each sub section
        $this->paymentsList[$paymentSectionNumber + 1][0]['amount'] = strval($sectionTotal - $amountToRemove);
        $this->totalMoneyOut -= $amountToRemove;
        unset($this->paymentsList[$paymentSectionNumber][$paymentNumber]);

        // if last remaining payment in the section is removed then unset the section and section total
        if (intval($this->paymentsList[$paymentSectionNumber + 1][0]['amount']) <= 0) {
            unset($this->paymentsList[$paymentSectionNumber + 1]);
            unset($this->paymentsList[$paymentSectionNumber]);
        }

        $this->paymentsList = array_values($this->paymentsList);
    }

    private function addPayment($payment, $anotherFlag)
    {
        $this->selectOption('account[category]', $payment['selectValue']);
        $this->pressButton('Save and continue');
        $this->fillInPaymentDetails($payment);
        $this->addAnother($anotherFlag);
    }

    private function fillInPaymentDetails($payment)
    {
        $this->iAmOnMoneyOutAddPaymentDetailsPage();
        $this->fillField('account[description]', $payment['description']);
        $this->fillField('account[amount]', $payment['amount']);
        if (array_key_exists('account', $payment)) {
            $this->iSelectBasedOnChoiceNumber('select', 'id', 'account_bankAccountId', 1);
        }
        $this->pressButton('Save and continue');
    }

    private function addAnother($anotherFlag)
    {
        $this->iAmOnMoneyOutAddAnotherPaymentPage();
        $this->selectOption('add_another[addAnother]', $anotherFlag);
        $this->pressButton('Save and continue');
    }

    private function getStringBetween($string, $start, $end)
    {
        $string = ' '.$string;
        $ini = strpos($string, $start);
        if (0 == $ini) {
            return '';
        }
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;

        return substr($string, $ini, $len);
    }

    private function formatPaymentObject($paymentObject)
    {
        $paymentObject['amount'] = $this->moneyFormat($paymentObject['amount']);
        unset($paymentObject['selectValue']);

        return array_values($paymentObject);
    }
}
