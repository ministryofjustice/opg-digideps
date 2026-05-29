<?php

namespace Tests\OPG\Digideps\Backend\Integration\ControllerReport;

use OPG\Digideps\Backend\Entity\Report\BankAccount;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\User;
use Tests\OPG\Digideps\Backend\Fixture\Scenario;
use Tests\OPG\Digideps\Backend\Integration\Controller\AbstractTestController;

class AccountControllerTest extends AbstractTestController
{
    private static Report $report1;
    private static BankAccount $account1;
    private static Report $report2;
    private static BankAccount $account2;
    private static BankAccount $account3;
    private static string $tokenAdmin;
    private static string $tokenDeputy;

    public function setUp(): void
    {
        parent::setUp();

        ['persons' => ['users' => ['lay1' => $user1]], 'orders' => [['pfa' => ['reports' => [self::$report1]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());
        ['orders' => [['pfa' => ['reports' => [self::$report2]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());

        self::$account1 = self::fixtures()->createAccount(self::$report1, ['setBank' => 'bank1']);
        self::$account2 = self::fixtures()->createAccount(self::$report2, ['setBank' => 'bank2']);
        self::$account3 = self::fixtures()->createAccount(self::$report1, ['setBank' => 'bank3']);

        // create an expense attached to account1 meaning account 1 cannot be removed
        self::fixtures()->createReportExpense(
            'other',
            self::$report1,
            [
                'setExplanation' => 'e1',
                'setAmount' => 1.1,
                'setBankAccount' => self::$account3,
            ]
        );

        self::fixtures()->flush()->clear();

        self::$tokenAdmin = $this->loginAsAdmin();
        self::$tokenDeputy = $this->loginAsDeputy($user1->getEmail());
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function testAddAccount()
    {
        $url = '/report/' . self::$report1->getId() . '/account';
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
        /* @var $account BankAccount */
        $account = self::fixtures()->getRepo(BankAccount::class)->find($return['data']['id']) ?? throw new \LogicException('Bad fixture setup');
        $this->assertNull($account->getUpdatedAt(), 'account.updatedAt must be null on creation');
        $this->assertFalse($account->getIsClosed());
        $this->assertNull($account->getIsJointAccount());

        // assert cannot create account for a report not belonging to logged user
        $url2 = '/report/' . self::$report2->getId() . '/account';
        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy);

        $this->assertArrayHasKey('state', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_BANK_ACCOUNTS));

        return $account->getId();
    }

    /**
     * @depends testAddAccount
     */
    public function testGetAccounts()
    {
        $data = $this->assertJsonRequest(
            'GET',
            sprintf('/report/%s?%s', self::$report1->getId(), http_build_query(['groups' => ['account']])),
            ['mustSucceed' => true, 'AuthToken' => self::$tokenDeputy]
        )['data']['bank_accounts'];

        $this->assertCount(2, $data);
        $this->assertTrue($data[0]['id'] != $data[1]['id']);
        $this->assertArrayHasKey('bank', $data[0]);
        $this->assertArrayHasKey('bank', $data[1]);
    }

    public function testGetOneById()
    {
        $url = '/report/account/' . self::$account1->getId();
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
        $url2 = '/report/account/' . self::$account2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    /**
     * @depends testGetOneById
     */
    public function testEdit()
    {
        $url = '/account/' . self::$account1->getId();
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

        $account = self::fixtures()->clear()->getRepo(BankAccount::class)->find(self::$account1->getId());
        $this->assertNotNull($account);
        $this->assertEquals('bank1-modified', $account->getBank());
        $this->assertTrue($account->getIsClosed());
        $this->assertEquals('yes', $account->getIsJointAccount());

        // assert user cannot modify another users' account
        $url2 = '/account/' . self::$account2->getId();
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);

        $this->assertEquals('incomplete', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_BANK_ACCOUNTS)['state']);
    }

    /**
     * @depends testEdit
     */
    public function testAccountDelete()
    {
        $account1Id = self::$account1->getId();
        $account = self::fixtures()->getRepo(BankAccount::class)->find(self::$account1->getId()); /* @var $account BankAccount */
        $report = $account->getReport();
        $report->setSectionStatusesCached([]);
        $url = '/account/' . $account1Id;
        $url2 = '/account/' . self::$account2->getId();
        $url3 = '/account/' . self::$account3->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);

        // assert user cannot delete another users' account
        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);

        // delete an account with associated transactions
        $this->assertJsonRequest('DELETE', $url3, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $freshAccount = self::fixtures()->clear()->getRepo(BankAccount::class)->find(self::$account3->getId());
        $this->assertNotInstanceOf(BankAccount::class, $freshAccount);

        $this->assertEquals('incomplete', self::fixtures()->getReportFreshSectionStatus(self::$report1, Report::SECTION_BANK_ACCOUNTS)['state']);
    }
}
