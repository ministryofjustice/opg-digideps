<?php

namespace App\Tests\Unit\Controller\Ndr;

use app\tests\Integration\Controller\AbstractTestController;

class AccountControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $ndr1;
    private static $account1;
    private static $deputy2;
    private static $ndr2;
    private static $account2;
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

        self::$ndr1 = self::fixtures()->createNdr($client1);
        self::$account1 = self::fixtures()->createNdrAccount(self::$ndr1, ['setBank' => 'bank1']);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        $client2 = self::fixtures()->createClient(self::$deputy2);
        self::$ndr2 = self::fixtures()->createNdr($client2);
        self::$account2 = self::fixtures()->createNdrAccount(self::$ndr2, ['setBank' => 'bank2']);

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

    public function testgetAccounts()
    {
        $this->markTestIncomplete('implement using ndr/1 with accounts group');
    }

    public function testaddAccount()
    {
        $url = '/ndr/'.self::$ndr1->getId().'/account';
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin);

        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'bank' => 'hsbc',
                'account_type' => 'savings',
                'sort_code' => '123456',
                'account_number' => '1234',
                'balance_on_court_order_date' => '500.45',
            ],
        ]);
        $this->assertTrue($return['data']['id'] > 0);

        self::fixtures()->clear();

        // assert account created with transactions
        $account = self::fixtures()->getRepo('Ndr\BankAccount')->find($return['data']['id']); /* @var $account \App\Entity\Ndr\BankAccount */
        $this->assertEquals('hsbc', $account->getBank());
        $this->assertEquals('savings', $account->getAccountType());
        $this->assertEquals('123456', $account->getSortCode());
        $this->assertEquals('500.45', $account->getBalanceOnCourtOrderDate());

        // assert cannot create account for a ndr not belonging to logged user
        $url2 = '/ndr/'.self::$ndr2->getId().'/account';
        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy);

        return $account->getId();
    }

    public function testgetOneById()
    {
        $url = '/ndr/account/'.self::$account1->getId();
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
        $this->assertEquals(self::$ndr1->getId(), $data['ndr']['id']);

        // assert  user2 cannot read the account
        $url2 = '/ndr/account/'.self::$account2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    /**
     * @depends testgetOneById
     */
    public function testEdit()
    {
        $url = '/ndr/account/'.self::$account1->getId();
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);

        // assert put
        $data = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'bank' => 'bank1-modified',
                'balance_on_court_order_date' => '499',
                'is_joint_account' => 'yes',
            ],
        ])['data'];

        $account = self::fixtures()->getRepo('Ndr\BankAccount')->find($data);
        $this->assertEquals('bank1-modified', $account->getBank());
        $this->assertEquals(499, $account->getBalanceOnCourtOrderDate());
        $this->assertEquals('yes', $account->getIsJointAccount());

        // assert user cannot modify another users' account
        $url2 = '/ndr/account/'.self::$account2->getId();
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);
    }

    /**
     * @depends testEdit
     */
    public function testaccountDelete()
    {
        $account1Id = self::$account1->getId();
        $url = '/ndr/account/'.$account1Id;
        $url2 = '/ndr/account/'.self::$account2->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);

        // assert user cannot delete another users' account
        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);

        // assert delete
        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        self::fixtures()->clear();

        $this->assertTrue(null === self::fixtures()->getRepo('Ndr\BankAccount')->find($account1Id));
    }
}
