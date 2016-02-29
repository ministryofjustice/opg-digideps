<?php

namespace AppBundle\Controller;

use AppBundle\Entity\MoneyTransfer;

class MoneyTransferTest extends AbstractTestController
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
        self::$account3 = self::fixtures()->createAccount(self::$report2, ['setBank'=>'bank3']);
        
        
        // add two transfer to report 2
        $transfer1 = new MoneyTransfer;
        $transfer1->setReport(self::$report2);
        $transfer1->setAmount(1001);
        $transfer1->setFrom(self::$account2);
        $transfer1->setTo(self::$account3);
        self::fixtures()->persist($transfer1);
        
        
        $transfer2 = new MoneyTransfer;
        $transfer2->setReport(self::$report2);
        $transfer2->setAmount(52);
        $transfer2->setFrom(self::$account3);
        $transfer2->setTo(self::$account2);
        self::fixtures()->persist($transfer2);
        
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
    
//    public function testgetAccountsAuth()
//    {
//        $url = '/report/' . self::$report1->getId() . '/account/' . self::$account1->getId() . '/transfer';
//        $this->assertEndpointNeedsAuth('GET', $url); 
//        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin); 
//    }
//    
//    public function testgetAccountsAcl()
//    {
//        $url2 = '/report/' . self::$report2->getId() . '/accounts';
//        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy); 
//    }
    
    public function testGetTransfers()
    {
        $url = '/report/' . self::$report1->getId() . '/transfers';
        
        // assert data is retrieved
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed'=>true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        print_r($data);die;
        
        
//        $this->assertCount(1, $data);
//        $this->assertEquals(self::$account1->getId(), $data[0]['id']);
//        $this->assertEquals(self::$account1->getBank(), $data[0]['bank']);
    }
   
    
//    public function testaddAccount()
//    {
//        $url = '/report/' . self::$report1->getId() . '/account';
//        $this->assertEndpointNeedsAuth('POST', $url); 
//        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin); 
//        
//        $return = $this->assertJsonRequest('POST', $url, [
//            'mustSucceed'=>true,
//            'AuthToken' => self::$tokenDeputy,
//            'data'=> [
//                'bank' => 'hsbc',
//                'sort_code' => '123456',
//                'account_number' => '1234',
//                'opening_date' => '01/01/2015',
//                'opening_balance' => '500'
//            ]
//        ]);
//        $this->assertTrue($return['data']['id'] > 0);
//        
//        self::fixtures()->clear();
//        
//        // assert account created with transactions
//        $account = self::fixtures()->getRepo('Account')->find($return['data']['id']); /* @var $account \AppBundle\Entity\Account */
//        $this->assertNull($account->getLastEdit(), 'account.lastEdit must be null on creation');
//        
//        // assert cannot create account for a report not belonging to logged user
//        $url2 = '/report/' . self::$report2->getId() . '/account';
//        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy); 
//        
//        return $account->getId();
//    }
//    
//    
//    public function testgetOneById()
//    {
//        $url = '/report/account/'.self::$account1->getId();
//        $this->assertEndpointNeedsAuth('GET', $url); 
//        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin); 
//        
//        // assert get
//        $data = $this->assertJsonRequest('GET', $url,[
//            'mustSucceed'=>true,
//            'AuthToken' => self::$tokenDeputy,
//        ])['data'];
//        $this->assertEquals('bank1', $data['bank']);
//        $this->assertEquals('101010', $data['sort_code']);
//        $this->assertEquals('1234', $data['account_number']);
//        $this->assertEquals(self::$report1->getId(), $data['report']['id']);
//
//        
//        // asser  user2 cannot read the account
//        $url2 = '/report/account/'.self::$account2->getId();
//        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy); 
//    }
//    
//    /**
//     * @depends testgetOneById
//     */
//    public function testEdit()
//    {
//        $url = '/account/' . self::$account1->getId();
//        $this->assertEndpointNeedsAuth('PUT', $url);
//        $this->assertEndpointNotAllowedFor('PUT', $url, self::$tokenAdmin); 
//        
//        // assert put
//        $data = $this->assertJsonRequest('PUT', $url,[
//            'mustSucceed'=>true,
//            'AuthToken' => self::$tokenDeputy,
//            'data' => [
//                'bank' => 'bank1-modified'
//                //TODO add other fields
//            ]
//        ])['data'];
//        
//        $account = self::fixtures()->getRepo('Account')->find(self::$account1->getId());
//        $this->assertEquals('bank1-modified', $account->getBank());
//        
//        // assert user cannot modify another users' account
//        $url2 = '/account/'.self::$account2->getId();
//        $this->assertEndpointNotAllowedFor('PUT', $url2, self::$tokenDeputy); 
//    }
//    
//    /**
//     * @depends testEdit
//     */
//    public function testaccountDelete()
//    {
//        $account1Id = self::$account1->getId();
//        $url = '/account/' . $account1Id;
//        $url2 = '/account/' .  self::$account2->getId();
//        
//        $this->assertEndpointNeedsAuth('DELETE', $url);
//        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenAdmin); 
//        
//        // assert user cannot delete another users' account
//        $this->assertEndpointNotAllowedFor('DELETE', $url2, self::$tokenDeputy); 
//        
//        // assert delete
//        $this->assertJsonRequest('DELETE', $url,[
//            'mustSucceed'=>true,
//            'AuthToken' =>self::$tokenDeputy,
//        ]);
//        
//        self::fixtures()->clear();
//
//        $this->assertTrue(null === self::fixtures()->getRepo('Account')->find($account1Id));
//    }
    
    
}
