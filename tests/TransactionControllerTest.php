<?php

namespace AppBundle\Controller;

class TransactionControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $report1;
    private static $account1;
    private static $deputy2;
    private static $report2;
    private static $account2;
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');

        $client1 = self::fixtures()->createClient(self::$deputy1);
        self::fixtures()->flush();

        self::$report1 = self::fixtures()->createReport($client1);
        self::$account1 = self::fixtures()->createAccount(self::$report1, ['setBank'=>'bank1']);


        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        $client2 = self::fixtures()->createClient(self::$deputy2);
        self::$report2 = self::fixtures()->createReport($client2);
        self::$account2 = self::fixtures()->createAccount(self::$report2, ['setBank'=>'bank2']);

        self::fixtures()->flush()->clear();
    }

    /**
     * clear fixtures
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

    public function testGetAllAuth()
    {
        $url = '/report/'.self::$report1->getId().'/transactions';
        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin);
    }

    public function testGetAllAcl()
    {
        $url = '/report/'.self::$report2->getId().'/transactions';

        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy);
    }

    public function testGetAll()
    {
        $url = '/report/'.self::$report1->getId().'/transactions';

        // assert data is retrieved
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed'=>true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];

        print_r($data);die;
        $this->assertCount(1, $data);
        $this->assertEquals(self::$account1->getId(), $data[0]['id']);
        $this->assertEquals(self::$account1->getBank(), $data[0]['bank']);
    }




    /**
     * @depends testgetOneById
     */
    public function testEdit()
    {
        $url = '/report/'.self::$report1->getId().'/transactions';
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin);

        // assert put
        $data = $this->assertJsonRequest('PUT', $url,[
            'mustSucceed'=>true,
            'AuthToken' => self::$tokenDeputy,
            'data' => [
                // 'money_in' //TODO
                // 'money_out' //TODO
                'bank' => 'bank1-modified'
                //TODO add other fields
            ]
        ])['data'];

        $account = self::fixtures()->getRepo('Account')->find(self::$account1->getId());
        $this->assertEquals('bank1-modified', $account->getBank());

        // assert user cannot modify another users' account
        $url2 = '/account/'.self::$account2->getId();
        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy);
    }

    /**
     * @depends testEdit
     */
    public function testaccountDelete()
    {
        $account1Id = self::$account1->getId();
        $url = '/account/' . $account1Id;
        $url2 = '/account/' .  self::$account2->getId();

        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin);

        // assert user cannot delete another users' account
        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy);

        // assert delete
        $this->assertJsonRequest('DELETE', $url,[
            'mustSucceed'=>true,
            'AuthToken' =>self::$tokenDeputy,
        ]);

        self::fixtures()->clear();

        $this->assertTrue(null === self::fixtures()->getRepo('Account')->find($account1Id));
    }


}
