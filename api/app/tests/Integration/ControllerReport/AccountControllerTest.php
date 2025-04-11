<?php

namespace App\Tests\Integration\ControllerReport;

use App\Entity\Report\BankAccount;
use App\Entity\Report\Report;
use App\Tests\Integration\Controller\AbstractTestController;

class AccountControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $report1;
    private static $account1;
    private static $deputy2;
    private static $report2;
    private static $account2;
    private static $account3;
    private static $expense1;
    private static $tokenAdmin;
    private static $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }

        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');

        $client1 = self::fixtures()->createClient(self::$deputy1);
        self::fixtures()->flush();

        self::$report1 = self::fixtures()->createReport($client1);
        self::$account1 = self::fixtures()->createAccount(self::$report1, ['setBank' => 'bank1']);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        $client2 = self::fixtures()->createClient(self::$deputy2);
        self::$report2 = self::fixtures()->createReport($client2);
        self::$account2 = self::fixtures()->createAccount(self::$report2, ['setBank' => 'bank2']);

        // create an expense attached to account1 meaning account 1 cannot be removed
        self::$account3 = self::fixtures()->createAccount(self::$report1, ['setBank' => 'bank3']);

        self::$expense1 = self::fixtures()->createReportExpense(
            'other',
            self::$report1,
            [
                'setExplanation' => 'e1',
                'setAmount' => 1.1,
                'setBankAccount' => self::$account3,
            ]
        );

        self::fixtures()->flush()->clear();
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function testaddAccount()
    {
        $url = '/report/'.self::$report1->getId().'/account';
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);

        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'bank' => 'hsbc',
                'sort_code' => '123456',
                'account_number' => '1234',
                'opening_balance' => '500',
            ],
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        // assert account created with transactions
        /* @var $account \App\Entity\Report\BankAccount */
        $account = self::fixtures()->getRepo('Report\BankAccount')->find($return['data']['id']);
        $this->assertNull($account->getUpdatedAt(), 'account.updatedAt must be null on creation');
        $this->assertFalse($account->getIsClosed());
        $this->assertNull($account->getIsJointAccount());

        // assert cannot create account for a report not belonging to logged user
        $url2 = '/report/'.self::$report2->getId().'/account';
        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy);

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_BANK_ACCOUNTS));

        return $account->getId();
    }

    /**
     * @depends testaddAccount
     */
    public function testgetAccounts()
    {
        $url = '/report/'.self::$report1->getId();

        // assert accounts
        $data = $this->assertJsonRequest('GET', $url . '?' . http_build_query(['groups' => ['account']]), [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data']['bank_accounts'];

        $this->assertCount(2, $data);
        $this->assertTrue($data[0]['id'] != $data[1]['id']);
        $this->assertArrayHasKey('bank', $data[0]);
        $this->assertArrayHasKey('bank', $data[1]);
    }

    public function testgetOneById()
    {
        $url = '/report/account/'.self::$account1->getId();
        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);

        // assert get
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        $this->assertEquals('bank1', $data['bank']);
        $this->assertEquals('101010', $data['sort_code']);
        $this->assertEquals('1234', $data['account_number']);

        // asser  user2 cannot read the account
        $url2 = '/report/account/'.self::$account2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    /**
     * @depends testgetOneById
     */
    public function testEdit()
    {
        $url = '/account/'.self::$account1->getId();
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);

        // assert put
        $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'bank' => 'bank1-modified',
                'opening_balance' => '500',
                'is_closed' => true,
                'is_joint_account' => 'yes',
            ],
        ])['data'];

        $account = self::fixtures()->clear()->getRepo('Report\BankAccount')->find(self::$account1->getId());
        $this->assertEquals('bank1-modified', $account->getBank());
        $this->assertTrue($account->getIsClosed());
        $this->assertEquals('yes', $account->getIsJointAccount());

        // assert user cannot modify another users' account
        $url2 = '/account/'.self::$account2->getId();
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);

        $this->assertEquals('incomplete', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_BANK_ACCOUNTS)['state']);
    }

    /**
     * @depends testEdit
     */
    public function testaccountDelete()
    {
        $account1Id = self::$account1->getId();
        $account = self::fixtures()->getRepo('Report\BankAccount')->find(self::$account1->getId()); /* @var $account BankAccount */
        $report = $account->getReport();
        $report->setSectionStatusesCached([]);
        $url = '/account/'.$account1Id;
        $url2 = '/account/'.self::$account2->getId();
        $url3 = '/account/'.self::$account3->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);

        // assert user cannot delete another users' account
        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);

        // delete an account with associated transactions
        $this->assertJsonRequest('DELETE', $url3, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $freshAccount = self::fixtures()->clear()->getRepo('Report\BankAccount')->find(self::$account3->getId());
        $this->assertNotInstanceOf(BankAccount::class, $freshAccount);

        $this->assertEquals('incomplete', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_BANK_ACCOUNTS)['state']);
    }
}
