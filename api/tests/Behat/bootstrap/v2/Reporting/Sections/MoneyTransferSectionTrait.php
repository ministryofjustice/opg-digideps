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
     * @Then /^I should be able to add a transfer between two accounts$/
     */
    public function iShouldBeAbleToAddATransferBetweenTwoAccounts()
    {
        $this->pressButton('Start money transfers');
        $this->selectOption('yes_no[noTransfersToAdd]', '0');
        $this->pressButton('Save and continue');

        $this->selectOption('money_transfers_type[accountFromId]', '3');
        $this->selectOption('money_transfers_type[accountToId]', '4');
        $this->fillInField('money_transfers_type[amount]', '£100.00');
        $this->pressButton('Save and continue');

        $this->selectOption('add_another[addAnother]', '1');
        $this->pressButton('Continue');
    }
}
