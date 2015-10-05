<?php

namespace AppBundle\Controller;

class AccountControllerTest extends AbstractTestController
{
    public function setUp()
    {
        parent::setUp();
        
//        $this->admin = $this->fixtures->getRepo('User')->findOneByEmail('admin@example.org');
        
        // deputy1 (logged) 
        $this->deputy1 = $this->fixtures->getRepo('User')->findOneByEmail('deputy@example.org');
        
        $client1 = $this->fixtures->createClient($this->deputy1);
        $this->fixtures->flush();
        
        $this->report1 = $this->fixtures->createReport($client1);
        $this->account1 = $this->fixtures->createAccount($this->report1, ['setBank'=>'bank1']);
        
        
        // deputy 2
        $this->deputy2 = $this->fixtures->createUser();
        $client2 = $this->fixtures->createClient($this->deputy2);
        $this->report2 = $this->fixtures->createReport($client2);
        $this->account2 = $this->fixtures->createAccount($this->report2, ['setBank'=>'bank2']);
        
        $this->fixtures->flush()->clear();
        
        $this->repo = $this->fixtures->getRepo('Account');
        
        $this->tokenAdmin = $this->loginAsAdmin();
        $this->tokenDeputy = $this->loginAsDeputy();
    }
    
    
    public function testgetAccountsAction()
    {
        $url = '/report/get-accounts/' . $this->report1->getId();
        $this->assertEndpointNeedsAuth('GET', $url); 
        $this->assertEndpointNotAllowedFor('GET', $url, $this->tokenAdmin); 
    
        // assert data is retrieved
        $data = $this->assertRequest('GET', $url, [
            'mustSucceed'=>true,
            'AuthToken' => $this->tokenDeputy,
        ])['data'];
        $this->assertCount(1, $data);
        $this->assertEquals($this->account1->getId(), $data[0]['id']);
        $this->assertEquals($this->account1->getBank(), $data[0]['bank']);
        
        //assert I cannot get data from user that is not logged
        $url2 = '/report/get-accounts/' . $this->report2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, $this->tokenDeputy); 
    }
   
    
    public function testaddAccount()
    {
        $url = '/report/' . $this->report1->getId() . '/add-account';
        $this->assertEndpointNeedsAuth('POST', $url); 
        $this->assertEndpointNotAllowedFor('POST', $url, $this->tokenAdmin); 
        
        $return = $this->assertRequest('POST', $url, [
            'mustSucceed'=>true,
            'AuthToken' => $this->tokenDeputy,
            'data'=> [
                'bank' => 'hsbc',
                'sort_code' => '123456',
                'account_number' => '1234',
                'opening_date' => '01/01/2015',
                'opening_balance' => '500'
            ]
        ]);
        $this->assertTrue($return['data']['id'] > 0);
        
        // assert account created with transactions
        $account = $this->repo->find($return['data']['id']); /* @var $account \AppBundle\Entity\Account */
        $transactionTypesTotal = count($this->fixtures->getRepo('AccountTransactionType')->findAll());
        $this->assertCount($transactionTypesTotal, $account->getTransactions(), "transactions not created");

        $this->assertNull($account->getLastEdit(), 'account.lastEdit must be null on creation');
        
        // assert cannot create account for a report not belonging to logged user
        $url2 = '/report/' . $this->report2->getId() . '/add-account';
        $this->assertEndpointNotAllowedFor('POST', $url2, $this->tokenDeputy); 
        
        return $account->getId();
    }
    
    
    public function testgetOneById()
    {
        $url = '/report/find-account-by-id/'.$this->account1->getId();
        $this->assertEndpointNeedsAuth('GET', $url); 
        $this->assertEndpointNotAllowedFor('GET', $url, $this->tokenAdmin); 
        
        // assert get
        $data = $this->assertRequest('GET', $url,[
            'mustSucceed'=>true,
            'AuthToken' => $this->tokenDeputy,
        ])['data'];
        $this->assertEquals('bank1', $data['bank']);
        $this->assertEquals('101010', $data['sort_code']);
        $this->assertEquals('1234', $data['account_number']);
        $this->assertEquals($this->report1->getId(), $data['report']['id']);
        $this->assertEquals('0', $data['money_total']);
        $this->assertEquals('0', $data['money_in_total']);
        $this->assertEquals('0', $data['money_out_total']);
        
        
        // asser  user2 cannot read the account
        $url2 = '/report/find-account-by-id/'.$this->account2->getId();
        $this->assertEndpointNotAllowedFor('GET', $url2, $this->tokenDeputy); 
    }
    
    /**
     * @depends testgetOneById
     */
    public function testEdit()
    {
        $url = '/account/' . $this->account1->getId();
        $this->assertEndpointNeedsAuth('PUT', $url);
        $this->assertEndpointNotAllowedFor('PUT', $url, $this->tokenAdmin); 
        
        // assert put
        $data = $this->assertRequest('PUT', $url,[
            'mustSucceed'=>true,
            'AuthToken' => $this->tokenDeputy,
            'data' => [
                // 'money_in' //TODO
                // 'money_out' //TODO
                'bank' => 'bank1-modified'
                //TODO add other fields
            ]
        ])['data'];
        
        $account = $this->repo->find($this->account1->getId());
        $this->assertEquals('bank1-modified', $account->getBank());
        
        // assert user cannot modify another users' account
        $url2 = '/account/'.$this->account2->getId();
        $this->assertEndpointNotAllowedFor('PUT', $url2, $this->tokenDeputy); 
    }
    
    /**
     * @depends testEdit
     */
    public function testaccountDelete()
    {
        $url = '/account/' . $this->account1->getId();
        $url2 = '/account/' . $this->account2->getId();
        
        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, $this->tokenAdmin); 
        
        // assert user cannot delete another users' account
        $this->assertEndpointNotAllowedFor('DELETE', $url2, $this->tokenDeputy); 
        
        // assert delete
        $this->assertRequest('DELETE', $url,[
            'mustSucceed'=>true,
            'AuthToken' => $this->tokenDeputy,
        ]);
        $this->assertNull($this->repo->find($this->account1->getId()));
    }
    
    
}
