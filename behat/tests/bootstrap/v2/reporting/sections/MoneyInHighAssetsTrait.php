<?php declare(strict_types=1);

namespace DigidepsBehat\v2\Reporting\Sections;

trait MoneyInHighAssetsTrait
{
    private $invalidSelectOptionError = 'Please choose an option';
    private $enterAmountError = 'Please enter an amount';
    private $invalidAmountError = 'The amount must be between £0.01 and £100,000,000,000';

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
     * @Then I select dividends
     */
    public function iSelectDividends()
    {
        $this->selectOption('account[category]', 'dividends');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I dont enter an amount I see a error
     */
    public function iDontEnterAnAmountISeeAError()
    {
        $this->pressButton('Save and continue');
        $this->assertOnErrorMessage($this->enterAmountError);
    }

    /**
     * @Then I enter an invalid amount I see a error
     */
    public function iEnterAnInvalidAmountISeeAError()
    {
        $this->fillField('account[amount]', '0');

        $this->pressButton('Save and continue');
        $this->assertOnErrorMessage($this->invalidAmountError);
    }

    /**
     * @When I enter a valid amount
     */
    public function iEnterAValidAmount()
    {
        $this->fillField('account[amount]', '1');

        $this->pressButton('Save and continue');
    }

    /**
     * @Then I dont add another item
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
     * @Then I add another item
     */
    public function iAddAnotherItem()
    {
        $this->selectOption('add_another[addAnother]', 'yes');
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I select state pension
     */
    public function iSelectStatePension()
    {
        $this->selectOption('account[category]', 'state-pension');
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should be on the summary page
     */
    public function iShouldBeOnTheSummaryPage()
    {
        assert($this->iShouldSeeTheMoneyInSummary());
    }

    /**
     * @Then I remove the dividends item
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
     * @Then I should be on the money in page and see entry deleted
     */
    public function iShouldBeOnTheMoneyInPageAndSeeEntryDeleted()
    {
        $entryDeletedText = $this->getSession()->getPage()->find('css', '.opg-alert__message > .govuk-body')->getText();
        assert("Entry deleted" == $entryDeletedText);
    }
}