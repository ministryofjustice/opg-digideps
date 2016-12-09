<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Controller\AbstractTestController;

class AccountControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $odr1;
    private static $account1;
    private static $deputy2;
    private static $odr2;
    private static $account2;
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');

        $client1 = self::fixtures()->createClient(self::$deputy1);
        self::fixtures()->flush();

        self::$odr1 = self::fixtures()->createOdr($client1);
        self::$account1 = self::fixtures()->createOdrAccount(self::$odr1, ['setBank' => 'bank1']);

        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        $client2 = self::fixtures()->createClient(self::$deputy2);
        self::$odr2 = self::fixtures()->createOdr($client2);
        self::$account2 = self::fixtures()->createOdrAccount(self::$odr2, ['setBank' => 'bank2']);

        self::fixtures()->flush()->clear();
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function setUp()
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }
    }

    public function testgetAccountsAuth()
    {
        $url = '/odr/'.self::$odr1->getId().'/accounts';
        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testgetAccountsAcl()
    {
        $url2 = '/odr/'.self::$odr2->getId().'/accounts';
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testgetAccounts()
    {
        // assert data is retrieved
        $data = $this->assertJsonRequest('GET', '/odr/'.self::$odr1->getId().'/accounts', [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        $this->assertCount(1, $data);
        $this->assertEquals(self::$account1->getId(), $data[0]['id']);
        $this->assertEquals(self::$account1->getBank(), $data[0]['bank']);
    }

    public function testaddAccount()
    {
        $url = '/odr/'.self::$odr1->getId().'/account';
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
        $account = self::fixtures()->getRepo('Odr\Account')->find($return['data']['id']); /* @var $account \AppBundle\Entity\Odr\Account */
        $this->assertEquals('hsbc', $account->getBank());
        $this->assertEquals('savings', $account->getAccountType());
        $this->assertEquals('123456', $account->getSortCode());
        $this->assertEquals('500.45', $account->getBalanceOnCourtOrderDate());


        // assert cannot create account for a odr not belonging to logged user
        $url2 = '/odr/'.self::$odr2->getId().'/account';
        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy);

        return $account->getId();
    }

    public function testgetOneById()
    {
        $url = '/odr/account/'.self::$account1->getId();
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
        $this->assertEquals(self::$odr1->getId(), $data['odr']['id']);

        // assert  user2 cannot read the account
        $url2 = '/odr/account/'.self::$account2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    /**
     * @depends testgetOneById
     */
    public function testEdit()
    {
        $url = '/odr/account/'.self::$account1->getId();
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);

        // assert put
        $data = $this->assertJsonRequest('PUT', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                'bank' => 'bank1-modified',
                'balance_on_court_order_date' => '499',
            ],
        ])['data'];

        $account = self::fixtures()->getRepo('Odr\Account')->find(self::$account1->getId());
        $this->assertEquals('bank1-modified', $account->getBank());
        $this->assertEquals(499, $account->getBalanceOnCourtOrderDate());

        // assert user cannot modify another users' account
        $url2 = '/odr/account/'.self::$account2->getId();
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);
    }

    /**
     * @depends testEdit
     */
    public function testaccountDelete()
    {
        $account1Id = self::$account1->getId();
        $url = '/odr/account/'.$account1Id;
        $url2 = '/odr/account/'.self::$account2->getId();

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

        $this->assertTrue(null === self::fixtures()->getRepo('Odr\Account')->find($account1Id));
    }
}
