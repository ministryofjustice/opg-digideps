<?php

namespace AppBundle\Controller;

class AccountControllerTest extends AbstractTestController
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
    
    public function setUp()
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }
    }
    
    public function testgetAccountsAuth()
    {
        $url = '/report/get-accounts/' . self::$report1->getId();
        $this->assertEndpointNeedsAuth('GET', $url); 
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin); 
    }
    
    public function testgetAccountsAcl()
    {
        $url2 = '/report/get-accounts/' . self::$report2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy); 
    }
    
    public function testgetAccounts()
    {
        // assert data is retrieved
        $data = $this->assertJsonRequest('GET', '/report/get-accounts/' . self::$report1->getId(), [
            'mustSucceed'=>true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        $this->assertCount(1, $data);
        $this->assertEquals(self::$account1->getId(), $data[0]['id']);
        $this->assertEquals(self::$account1->getBank(), $data[0]['bank']);
    }
   
    
    public function testaddAccount()
    {
        $url = '/report/' . self::$report1->getId() . '/add-account';
        $this->assertEndpointNeedsAuth('POST', $url); 
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenAdmin); 
        
        $return = $this->assertJsonRequest('POST', $url, [
            'mustSucceed'=>true,
            'AuthToken' => self::$tokenDeputy,
            'data'=> [
                'bank' => 'hsbc',
                'sort_code' => '123456',
                'account_number' => '1234',
                'opening_date' => '01/01/2015',
                'opening_balance' => '500'
            ]
        ]);
        $this->assertTrue($return['data']['id'] > 0);
        
        self::fixtures()->clear();
        
        // assert account created with transactions
        $account = self::fixtures()->getRepo('Account')->find($return['data']['id']); /* @var $account \AppBundle\Entity\Account */
        $transactionTypesTotal = count(self::fixtures()->getRepo('AccountTransactionType')->findAll());
        $this->assertCount($transactionTypesTotal, $account->getTransactions(), "transactions not created");

        $this->assertNull($account->getLastEdit(), 'account.lastEdit must be null on creation');
        
        // assert cannot create account for a report not belonging to logged user
        $url2 = '/report/' . self::$report2->getId() . '/add-account';
        $this->assertEndpointNotAllowedFor('POST', $url2, self::$tokenDeputy); 
        
        return $account->getId();
    }
    
    
    public function testgetOneById()
    {
        $url = '/report/find-account-by-id/'.self::$account1->getId();
        $this->assertEndpointNeedsAuth('GET', $url); 
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenAdmin); 
        
        // assert get
        $data = $this->assertJsonRequest('GET', $url,[
            'mustSucceed'=>true,
            'AuthToken' => self::$tokenDeputy,
        ])['data'];
        $this->assertEquals('bank1', $data['bank']);
        $this->assertEquals('101010', $data['sort_code']);
        $this->assertEquals('1234', $data['account_number']);
        $this->assertEquals(self::$report1->getId(), $data['report']['id']);
        $this->assertEquals('0', $data['money_total']);
        $this->assertEquals('0', $data['money_in_total']);
        $this->assertEquals('0', $data['money_out_total']);
        
        
        // asser  user2 cannot read the account
        $url2 = '/report/find-account-by-id/'.self::$account2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, self::$tokenDeputy); 
    }
    
    /**
     * @depends testgetOneById
     */
    public function testEdit()
    {
        $url = '/account/' . self::$account1->getId();
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
