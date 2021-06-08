<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait AccountsSectionTrait
{
    private array $accountList = [];

    /**
     * @When I view and start the accounts report section
     */
    public function iViewAndStartAccountsSection()
    {
        $this->iViewAccountsSection();
        $this->clickLink('Start accounts');
    }

    /**
     * @When I go to add a new current account
     */
    public function iGoToAddNewCurrentAccount()
    {
        $account = [
            'account' => 'current',
            'accountType' => 'current account',
            'name' => 'account-1',
            'accountNumber' => '1111',
            'sortCode' => '01-01-01',
            'joint' => 'no',
            'openingBalance' => '801',
            'closingBalance' => '901',
        ];

        $this->accountList[] = $account;
        $this->visitPath($this->getAccountsAddAnAccountUrl($this->loggedInUserDetails->getCurrentReportId()));
        $this->iAmOnAccountsAddInitialPage();
        $this->iChooseAccountType('current');
    }

    /**
     * @When I miss one of the fields
     */
    public function iMissOneOfTheFields()
    {
        $this->iFillInAccountDetails(
            '1111',
            '01-01-01',
            '',
            'account-1'
        );

        $this->assertOnErrorMessage('Please select either \'Yes\' or \'No\'');

        $this->iFillInAccountDetails(
            '',
            '01-01-01',
            'no',
            'account-1'
        );

        $this->assertOnErrorMessage('Enter the last 4 digits of the account number');

        $this->iFillInAccountDetails(
            '1111',
            '',
            'no',
            'account-1'
        );

        $this->assertOnErrorMessage('The sort code should only contain numbers');
        $this->assertOnErrorMessage('The sort code must be 6 numbers long');

        $this->iFillInAccountDetails(
            '1111',
            '01-01-01',
            'no',
            ''
        );

        $this->assertOnErrorMessage('Enter the bank or building society name');
    }

    /**
     * @When I get the correct validation warnings
     */
    public function iGetTheCorrectValidationResponses()
    {
        $this->iAmOnAccountsDetailsPage();
    }

    /**
     * @When I try to enter letters where it should be digits
     */
    public function iTryToEnterLettersInsteadOfDigits()
    {
        $this->iFillInAccountDetails(
            '1111',
            'AA-BB-CC',
            'no',
            'account-1'
        );

        $this->assertOnErrorMessage('The sort code should only contain numbers');
    }

    /**
     * @When I correctly enter account details
     */
    public function iCorrectlyEnterAccountDetails()
    {
        $this->accountList[] =
            [
                'account' => 'current',
                'accountType' => 'current account',
                'name' => 'account-1',
                'accountNumber' => '1111',
                'sortCode' => '01-01-01',
                'joint' => 'no',
                'openingBalance' => '801',
                'closingBalance' => '901',
            ];

        $this->iFillInAccountDetails(
            $this->accountList[0]['accountNumber'],
            $this->accountList[0]['sortCode'],
            $this->accountList[0]['joint'],
            $this->accountList[0]['name']
        );

        $this->iFillInAccountBalance(
            $this->accountList[0]['openingBalance'],
            $this->accountList[0]['closingBalance']
        );

        $this->iAmOnAccountsAddAnotherPage();
        $this->selectOption('add_another[addAnother]', 'no');
        $this->pressButton('Continue');
    }

    /**
     * @When I update my current account to a different one
     */
    public function iUpdateCurrentAccountToDifferentOne()
    {
        $this->accountList[] =
            [
                'account' => 'current',
                'accountType' => 'current account',
                'name' => 'account-2',
                'accountNumber' => '2222',
                'sortCode' => '02-02-02',
                'joint' => 'no',
                'openingBalance' => '802',
                'closingBalance' => '902',
            ];

        $urlRegex = sprintf('/%s\/.*\/bank-account\/step1\/[0-9].*$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 0);
        $this->iAmOnAccountsAddInitialPage();
        $this->iAddAnAccount(
            $this->accountList[0]['account'],
            $this->accountList[0]['name'],
            $this->accountList[0]['accountNumber'],
            $this->accountList[0]['sortCode'],
            $this->accountList[0]['joint'],
            $this->accountList[0]['openingBalance'],
            $this->accountList[0]['closingBalance'],
        );
        $this->iAmOnAccountsSummaryPage();
    }

    /**
     * @When I add one of each account type with valid details
     */
    public function iAddOneOfEachTypeOfAccounts()
    {
        $this->accountList = [
            [
                'account' => 'current',
                'accountType' => 'current account',
                'name' => 'account-1',
                'accountNumber' => '1111',
                'sortCode' => '01-01-01',
                'joint' => 'no',
                'openingBalance' => '801',
                'closingBalance' => '901',
            ],
            [
                'account' => 'savings',
                'accountType' => 'savings account',
                'name' => 'account-2',
                'accountNumber' => '2222',
                'sortCode' => '02-02-02',
                'joint' => 'yes',
                'openingBalance' => '802',
                'closingBalance' => '902',
            ],
            [
                'account' => 'isa',
                'accountType' => 'isa',
                'name' => 'account-3',
                'accountNumber' => '3333',
                'sortCode' => '03-03-03',
                'joint' => 'no',
                'openingBalance' => '803',
                'closingBalance' => '903',
            ],
            [
                'account' => 'postoffice',
                'accountType' => 'post office account',
                'name' => '',
                'accountNumber' => '4444',
                'sortCode' => '',
                'joint' => 'yes',
                'openingBalance' => '804',
                'closingBalance' => '904',
            ],
            [
                'account' => 'cfo',
                'accountType' => 'court funds office account',
                'name' => '',
                'accountNumber' => '5555',
                'sortCode' => '',
                'joint' => 'no',
                'openingBalance' => '805',
                'closingBalance' => '905',
            ],
            [
                'account' => 'other',
                'accountType' => 'other',
                'name' => 'account-6',
                'accountNumber' => '6666',
                'sortCode' => '06-06-06',
                'joint' => 'yes',
                'openingBalance' => '806',
                'closingBalance' => '906',
            ],
            [
                'account' => 'other_no_sortcode',
                'accountType' => 'other without sort code',
                'name' => 'account-7',
                'accountNumber' => '7777',
                'sortCode' => '',
                'joint' => 'no',
                'openingBalance' => '807',
                'closingBalance' => '907',
            ],
        ];

        foreach ($this->accountList as $account) {
            $this->visitPath($this->getAccountsAddAnAccountUrl($this->loggedInUserDetails->getCurrentReportId()));
            $this->iAddAnAccount(
                $account['account'],
                $account['name'],
                $account['accountNumber'],
                $account['sortCode'],
                $account['joint'],
                $account['openingBalance'],
                $account['closingBalance'],
            );
        }

        if ('ndr' == $this->reportUrlPrefix) {
            $this->accountList = array_reverse($this->accountList);
        }
        $this->iAmOnAccountsAddAnotherPage();
        $this->selectOption('add_another[addAnother]', 'no');
        $this->pressButton('Continue');
    }

    /**
     * @Then I should see the expected accounts on the summary page
     */
    public function iShouldSeeTheExpectedAccountsOnSummaryPage()
    {
        $expectedList = $this->accountList;

        foreach ($expectedList as $expectedItemKey => $expectedItem) {
            $expectedList[$expectedItemKey]['sortCode'] = str_replace('-', '', $expectedList[$expectedItemKey]['sortCode']);
            unset($expectedList[$expectedItemKey]['account']);
            if ('ndr' == $this->reportUrlPrefix) {
                unset($expectedList[$expectedItemKey]['closingBalance']);
            }
        }
        $expectedList = array_values($expectedList);

        $this->expectedResultsDisplayed(
            0,
            $expectedList,
            'Accounts Details'
        );
    }

    /**
     * @When I add a couple of new accounts
     */
    public function iAddACoupleOfNewAccounts()
    {
        $this->accountList = [
            [
                'account' => 'current',
                'accountType' => 'current account',
                'name' => 'account-1',
                'accountNumber' => '1111',
                'sortCode' => '01-01-01',
                'joint' => 'no',
                'openingBalance' => '101',
                'closingBalance' => '201',
            ],
            [
                'account' => 'current',
                'accountType' => 'current account',
                'name' => 'account-2',
                'accountNumber' => '2222',
                'sortCode' => '02-02-02',
                'joint' => 'yes',
                'openingBalance' => '102',
                'closingBalance' => '202',
            ],
        ];
        foreach ($this->accountList as $account) {
            $this->visitPath($this->getAccountsAddAnAccountUrl($this->loggedInUserDetails->getCurrentReportId()));
            $this->iAddAnAccount(
                $account['account'],
                $account['name'],
                $account['accountNumber'],
                $account['sortCode'],
                $account['joint'],
                $account['openingBalance'],
                $account['closingBalance'],
            );
        }

        if ('ndr' == $this->reportUrlPrefix) {
            $this->accountList = array_reverse($this->accountList);
        }
        $this->iAmOnAccountsAddAnotherPage();
        $this->selectOption('add_another[addAnother]', 'no');
        $this->pressButton('Continue');
    }

    /**
     * @When I remove the second account
     */
    public function iRemoveTheSecondAccount()
    {
        $this->iRemoveAnAccount(1);
    }

    /**
     * @When I remove the remaining account
     */
    public function iRemoveTheRemainingAccount()
    {
        $this->iRemoveAnAccount(0);
    }

    public function iAddAnAccount(
        string $account,
        string $name,
        string $accountNumber,
        string $sortCode,
        string $joint,
        string $openingBalance,
        string $closingBalance
    ) {
        $this->iChooseAccountType($account);
        $this->iFillInAccountDetails($accountNumber, $sortCode, $joint, $name);
        $this->iFillInAccountBalance($openingBalance, $closingBalance);
    }

    public function iChooseAccountType(string $account)
    {
        $this->iSelectRadioBasedOnName('div', 'data-module', 'govuk-radios', $account);
        $this->pressButton('Save and continue');
    }

    public function iFillInAccountDetails(string $accountNumber, string $sortCode, string $joint, string $name)
    {
        if ($this->elementExistsOnPage('input', 'name', 'account[bank]')) {
            $this->fillField('account[bank]', $name);
        }

        $this->fillField('account[accountNumber]', $accountNumber);

        if ($this->elementExistsOnPage('input', 'name', 'account[sortCode][sort_code_part_1]')) {
            $this->fillField('account[sortCode][sort_code_part_1]', explode('-', $sortCode)[0]);
            $this->fillField('account[sortCode][sort_code_part_2]', explode('-', $sortCode)[1]);
            $this->fillField('account[sortCode][sort_code_part_3]', explode('-', $sortCode)[2]);
        }

        if (strlen($joint) > 0) {
            $this->iSelectRadioBasedOnName('div', 'data-module', 'govuk-radios', $joint);
        }

        $this->pressButton('Save and continue');
    }

    public function iFillInAccountBalance(string $openingBalance, string $closingBalance)
    {
        if ('ndr' == $this->reportUrlPrefix) {
            $this->fillField('account[balanceOnCourtOrderDate]', $openingBalance);
        } else {
            $this->fillField('account[openingBalance]', $openingBalance);
            $this->fillField('account[closingBalance]', $closingBalance);
        }

        $this->pressButton('Save and continue');
    }

    public function iRemoveAnAccount($accountOccurrence)
    {
        $this->iAmOnAccountsSummaryPage();

        // Remove the account from our array
        unset($this->accountList[$accountOccurrence]);
        $this->accountList = array_values($this->accountList);

        // Remove the account from the app
        $urlRegex = sprintf('/%s\/.*\/bank-account\/.*\/delete$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, $accountOccurrence);

        $this->iAmOnAccountsDeletePage();
        $this->pressButton('Yes, remove account');
    }
}
