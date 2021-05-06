<?php

declare(strict_types=1);

namespace DigidepsBehat\v2\Reporting\Sections;

trait MoneyInHighAssetsTrait
{
    // Expected valudation errors
    private string $invalidSelectOptionError = 'Please choose an option';
    private string $enterAmountError = 'Please enter an amount';
    private string $invalidAmountError = 'The amount must be between £0.01 and £100,000,000,000';

    // Values
    private string $amountValue = '£1.00';
    private string $updatedAmountValue = '£2.00';

    /**
     * @When I view the money in report section
     */
    public function iViewMoneyInSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $activeReportId, 'money-in');
        $this->visitPath($reportSectionUrl);
    }

    /**
     * @When I view and start the money in report section
     */
    public function iViewAndStartMoneyInSection()
    {
        $this->iViewMoneyInSection();
        $this->clickLink('Start money in');
    }

    /**
     * @Then I click save and continue
     */
    public function iClickSaveAndContinue()
    {
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see a select option error
     */
    public function iShouldSeeASelectOptionError()
    {
        $this->assertOnErrorMessage($this->invalidSelectOptionError);
    }

    /**
     * @And I have a dividend to report on
     */
    public function iHaveADividendToReportOn()
    {
        $this->selectOption('account[category]', 'dividends');
        $this->pressButton('Save and continue');
    }

    /**
     * @And I try to submit an empty amount
     */
    public function iTryToSubmitAnEmptyAmount()
    {
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see an empty field error
     */
    public function iShouldSeeAnEmptyFieldError()
    {
        $this->assertOnErrorMessage($this->enterAmountError);
    }

    /**
     * @And I try to submit an invalid amount
     */
    public function iTryToSubmitAnInvalidAmount()
    {
        $this->fillField('account[amount]', '0');

        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see an invalid field error
     */
    public function iShouldSeeAnInvalidFieldError()
    {
        $this->assertOnErrorMessage($this->invalidAmountError);
    }

    /**
     * @And I enter a valid amount
     */
    public function iEnterAValidAmount()
    {
        $this->fillField('account[amount]', '1');

        $this->pressButton('Save and continue');
    }

    /**
     * @And I dont add another item
     */
    public function iDontAddAnotherItem()
    {
        $this->selectOption('add_another[addAnother]', 'no');
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see the money in summary
     */
    public function iShouldSeeTheMoneyInSummary(): bool
    {
        return $this->iAmOnPage('/report\/.*\/money-in\/summary$/');
    }

    /**
     * @When I add another item
     */
    public function iAddAnotherItem()
    {
        $this->selectOption('add_another[addAnother]', 'yes');
        $this->pressButton('Save and continue');
    }

    /**
     * @And I select state pension
     */
    public function iSelectStatePension()
    {
        $this->selectOption('account[category]', 'state-pension');
        $this->pressButton('Save and continue');
    }

    /**
     * @Then the money in summary page should contain the money in values I added
     */
    public function theMoneyInSummaryPageShouldContainTheMoneyInValuesIAdded()
    {
        assert($this->iShouldSeeTheMoneyInSummary());

        $descriptionLists = $this->getSession()->getPage()->findAll('css', 'dl');
        if (0 === count($descriptionLists)) {
            $this->throwContextualException('A dl element was not found on the page - make sure the current url is as expected');
        }
    }

    /**
     * @And I remove the dividends item
     */
    public function iRemoveTheDividendsItem()
    {
        $this->clickLink('Remove');
        assert($this->iShouldBeOnTheDeletePage());
        $this->pressButton('Yes, remove item of income');
    }

    /**
     * @Then I should be on the delete page
     */
    public function iShouldBeOnTheDeletePage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/money-in\/.*\/delete$/');
    }

    /**
     * @Then I should be on the money in summary page and see entry deleted
     */
    public function iShouldBeOnTheMoneyInPageAndSeeEntryDeleted()
    {
        $entryDeletedText = $this->getSession()->getPage()->find('css', '.opg-alert__message > .govuk-body')->getText();
        assert('Entry deleted' == $entryDeletedText);
    }

    /**
     * @When I edit the money in value
     */
    public function iEditTheMoneyInValue()
    {
        $this->clickLink('Edit');
        $this->fillField('account[amount]', '2');
        $this->pressButton('Save and continue');
    }

    /**
     * @Then the money in summary page should contain the edited value
     */
    public function theMoneyInSummaryPageShouldContainTheEditedValue()
    {
        assert($this->iShouldSeeTheMoneyInSummary());

        $descriptionLists = $this->getSession()->getPage()->findAll('css', 'dl');
        if (0 === count($descriptionLists)) {
            $this->throwContextualException('A dl element was not found on the page - make sure the current url is as expected');
        }

        $invalidAmount = false;
        $editedAmount = '';
        foreach ($descriptionLists as $descriptionList) {
            $html = $descriptionList->getHtml();
            $textVisible = str_contains($html, $this->updatedAmountValue);

            if (!$textVisible) {
                $editedAmount = $textVisible;
                $invalidAmount = true;
            } else {
                break;
            }
        }

        if ($invalidAmount) {
            $this->throwContextualException(
                sprintf(
                    'A dl was found but the row with the expected text was not found. Missing text: %s. HTML found: %s',
                    $this->updatedAmountValue,
                    $editedAmount,
                    $html
                )
            );
        }
    }
}
