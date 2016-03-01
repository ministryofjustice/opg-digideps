<?php

namespace AppBundle\Controller;

use AppBundle\Entity\MoneyTransfer;

class MoneyTransferControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $report1;
    private static $account1;
    private static $deputy2;
    private static $report2;
    private static $account2;
    private static $account3;
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
        self::$account2 = self::fixtures()->createAccount(self::$report1, ['setBank'=>'bank2']);
        
        // add two transfer to report 1 between accounts
        $transfer1 = new MoneyTransfer;
        $transfer1->setReport(self::$report1)
            ->setAmount(1001)
            ->setFrom(self::$account2)
            ->setTo(self::$account1);
        self::fixtures()->persist($transfer1);
        
        $transfer2 = new MoneyTransfer;
        $transfer2->setReport(self::$report1)
            ->setAmount(52)
            ->setFrom(self::$account1)
            ->setTo(self::$account2);
        self::fixtures()->persist($transfer2);
        
        // deputy 2
        self::$deputy2 = self::fixtures()->createUser();
        $client2 = self::fixtures()->createClient(self::$deputy2);
        self::$report2 = self::fixtures()->createReport($client2);
        self::$account3 = self::fixtures()->createAccount(self::$report2, ['setBank'=>'bank3']);
        
        
        
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
    
    public function testGetTransfers()
    {
        $url = '/report/' . self::$report1->getId() . '?groups=transfers';
        
        // assert data is retrieved
        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed'=>true,
            'AuthToken' => self::$tokenDeputy,
        ])['data']['money_transfers'];
        
        $this->assertEquals(1001, $data[0]['amount']);
        $this->assertEquals('bank2', $data[0]['from']['bank']);
        $this->assertEquals('bank1', $data[0]['to']['bank']);
        
        $this->assertEquals(52, $data[1]['amount']);
        $this->assertEquals('bank1', $data[1]['from']['bank']);
        $this->assertEquals('bank2', $data[1]['to']['bank']);
    }
   
    
}
