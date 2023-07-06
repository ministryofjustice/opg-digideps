<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait MoneyTransferSectionTrait
{
    /**
     * @Then /^I should not be able to add a transfer due to having fewer than two accounts$/
     */
    public function iShouldNotBeAbleToAddATransferDueToHavingFewerThanTwoAccounts()
    {
        $expectedMessage = 'You do not need to complete this section if you have fewer than two bank accounts.';
        $this->assertElementContainsText('body', $expectedMessage);
    }

    /**
     * @Given /^I confirm that I have a transfer to add$/
     */
    public function iConfirmThatIHaveATransferToAdd()
    {
        $this->pressButton('Start money transfers');

        $this->iAmOnMoneyTransfersExistPage();

        $this->selectOption('yes_no[noTransfersToAdd]', '0');
        $this->pressButton('Save and continue');

        $this->iAmOnMoneyTransfersAddPage();
    }

    /**
     * @Then /^I add the transfer details between two accounts$/
     */
    public function iAddTheTransferDetailsBetweenTwoAccounts()
    {
        $this->selectOption('money_transfers_type[accountFromId]', '(****1234)');
        $this->selectOption('money_transfers_type[accountToId]', 'account-1 - Current account (****1111)');
        $this->fillInField('money_transfers_type[amount]', '100.00');
        $this->pressButton('Save and continue');

        $this->addAnotherTransfer('no');
    }

    /**
     * @Then /^I add the transfer details between two accounts with a description of (\d+) characters$/
     */
    public function iAddTheTransferDetailsBetweenTwoAccountsWithADescriptionOfCharacters($arg1)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $arg1; ++$i) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        $this->selectOption('money_transfers_type[accountFromId]', '(****1234)');
        $this->selectOption('money_transfers_type[accountToId]', 'account-1 - Current account (****1111)');
        $this->fillInField('money_transfers_type[amount]', '100.00');
        $this->fillinField('money_transfers_type[description]', $randomString);
        $this->pressButton('Save and continue');

        $this->addAnotherTransfer('no');
    }

        private function addAnotherTransfer($anotherFlag)
        {
            $this->iAmOnMoneyTransfersAddAnotherPage();

            $this->chooseOption('add_another[addAnother]', $anotherFlag);
            $this->pressButton('Continue');
        }

    /**
     * @Then /^I should see the transfer listed on the money transfers summary page$/
     */
    public function iShouldSeeTheExpectedResultsOnMoneyTransfersSummaryPage()
    {
        $this->iAmOnMoneyTransfersSummaryPage();

        if ($this->getSectionAnswers('yes_no[noTransfersToAdd]')) {
            $this->expectedResultsDisplayedSimplified('noTransfersToAdd', true);
        }

        if ($this->getSectionAnswers('money_transfers_type[accountFromId]')) {
            $this->expectedResultsDisplayedSimplified('accountFromId', true);
        }

        if ($this->getSectionAnswers('money_transfers_type[accountToId]')) {
            $this->expectedResultsDisplayedSimplified('accountToId', true);
        }

        if ($this->getSectionAnswers('money_transfers_type[amount]')) {
            $this->expectedResultsDisplayedSimplified('amount', true);
        }

        if ($this->getSectionAnswers('money_transfers_type[description]')) {
            $this->expectedResultsDisplayedSimplified('description', true);
        }
    }

    /**
     * @Then /^I remove the money transfer I just added$/
     */
    public function iRemoveTheMoneyTransferIJustAdded()
    {
        $this->clickLink('Remove');
        $this->iShouldBeOnTheMoneyTransferDeletePage();
        $this->pressButton('Yes, remove transfer');
    }

    /**
     * @Then I should be on the money transfer delete page
     */
    public function iShouldBeOnTheMoneyTransferDeletePage(): bool
    {
        return $this->iAmOnPage('/report\/.*\/money-transfers\/.*\/delete$/');
    }

    /**
     * @Then /^I should be on the money transfers starting page and see entry deleted$/
     */
    public function iShouldBeOnTheMoneyTransfersStartingPageAndSeeEntryDeleted()
    {
        $this->iAmOnPage(sprintf('/%s\/.*\/money-transfers.*$/', $this->reportUrlPrefix));

        $entryDeletedText = $this->getSession()->getPage()->find('css', '.opg-alert__message > .govuk-body')->getText();
        assert('Money transfer deleted' == $entryDeletedText);
    }
}
