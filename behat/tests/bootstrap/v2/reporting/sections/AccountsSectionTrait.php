<?php

declare(strict_types=1);

namespace DigidepsBehat\v2\Reporting\Sections;

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
            'openingBalance' => '101',
            'closingBalance' => '201',
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
                'openingBalance' => '101',
                'closingBalance' => '201',
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
                'openingBalance' => '102',
                'closingBalance' => '202',
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
                'openingBalance' => '101',
                'closingBalance' => '201',
            ],
            [
                'account' => 'savings',
                'accountType' => 'savings account',
                'name' => 'account-2',
                'accountNumber' => '2222',
                'sortCode' => '02-02-02',
                'joint' => 'yes',
                'openingBalance' => '102',
                'closingBalance' => '202',
            ],
            [
                'account' => 'isa',
                'accountType' => 'isa',
                'name' => 'account-3',
                'accountNumber' => '3333',
                'sortCode' => '03-03-03',
                'joint' => 'no',
                'openingBalance' => '103',
                'closingBalance' => '203',
            ],
            [
                'account' => 'postoffice',
                'accountType' => 'post office account',
                'name' => '',
                'accountNumber' => '4444',
                'sortCode' => '',
                'joint' => 'yes',
                'openingBalance' => '104',
                'closingBalance' => '204',
            ],
            [
                'account' => 'cfo',
                'accountType' => 'court funds office account',
                'name' => '',
                'accountNumber' => '5555',
                'sortCode' => '',
                'joint' => 'no',
                'openingBalance' => '105',
                'closingBalance' => '205',
            ],
            [
                'account' => 'other',
                'accountType' => 'other',
                'name' => 'account-6',
                'accountNumber' => '6666',
                'sortCode' => '06-06-06',
                'joint' => 'yes',
                'openingBalance' => '106',
                'closingBalance' => '206',
            ],
            [
                'account' => 'other_no_sortcode',
                'accountType' => 'other without sort code',
                'name' => 'account-7',
                'accountNumber' => '7777',
                'sortCode' => '',
                'joint' => 'no',
                'openingBalance' => '107',
                'closingBalance' => '207',
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
        $this->iAmOnAccountsAddAnotherPage();
        $this->selectOption('add_another[addAnother]', 'no');
        $this->pressButton('Continue');
    }

    /**
     * @Then I should see the expected accounts on the summary page
     */
    public function iShouldSeeTheExpectedAccountsOnSummaryPage()
    {
        $isNdr = 'ndr' == $this->reportUrlPrefix ? true : false;

        $accountSummaryElems = [
          'tableBody' => $isNdr ? 'dl' : 'tbody',
          'row' => $isNdr ? 'div.govuk-summary-list__row' : 'tr',
          'head' => $isNdr ? 'dt' : 'th',
          'data' => $isNdr ? 'dd' : 'td',
        ];

        $this->iAmOnAccountsSummaryPage();

        $tableBody = $this->getSession()->getPage()->find('css', $accountSummaryElems['tableBody']);

        if (!$tableBody) {
            $this->throwContextualException('A tbody element was not found on the page');
        }

        $tableRows = $tableBody->findAll('css', $accountSummaryElems['row']);

        if (!$tableRows) {
            $this->throwContextualException('A tr element was not found on the page');
        }

        if ($isNdr) {
            unset($tableRows[0]);
            $tableRows = array_values($tableRows);
        }

        foreach ($tableRows as $tRowKey => $tableRow) {
            $tableHeader = $tableRow->find('css', $accountSummaryElems['head']);
            $headHtml = trim(strtolower($tableHeader->getHtml()));
            $this->assertStringContainsString($this->accountList[$tRowKey]['accountType'], $headHtml, 'Accounts Type');
            $this->assertStringContainsString($this->accountList[$tRowKey]['name'], $headHtml, 'Accounts Name');
            $this->assertStringContainsString($this->accountList[$tRowKey]['accountNumber'], $headHtml, 'Accounts Number');

            $sortCode = str_replace('-', '', $this->accountList[$tRowKey]['sortCode']);
            $this->assertStringContainsString($sortCode, $headHtml, 'Accounts Sort Code');
            $this->assertStringContainsString($this->accountList[$tRowKey]['joint'], $headHtml, 'Accounts Joint');

            $tableFields = $tableRow->findAll('css', $accountSummaryElems['data']);

            foreach ($tableFields as $tFieldKey => $tableField) {
                $balanceItem = trim(strtolower($tableField->getHtml()));
                if (0 == $tFieldKey) {
                    $this->assertStringContainsString($this->accountList[$tRowKey]['openingBalance'], $balanceItem, 'Accounts Opening Balance');
                } elseif (1 == $tFieldKey and 'ndr' != $this->reportUrlPrefix) {
                    $this->assertStringContainsString($this->accountList[$tRowKey]['closingBalance'], $balanceItem, 'Accounts Closing Balance');
                }
            }
        }
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
