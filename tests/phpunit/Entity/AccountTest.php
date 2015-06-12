<?php
namespace AppBundle\Entity;

class AccountTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
    }

    public function testSetterGetters()
    {
        $account = new Account;
        $account->setLastEdit(new \DateTime('2015-01-01'));
        $this->assertEquals('123456', $account->setAccountNumber('123456')->getAccountNumber());
        $this->assertEquals('123456', $account->setBank('123456')->getBank());
        $this->assertEquals('123456', $account->setClosingBalance('123456')->getClosingBalance());
        $this->assertEquals('123456', $account->setOpeningBalance('123456')->getOpeningBalance());
    }
    
    public function testTotals()
    {
        $account = new Account();
        $account->setOpeningBalance(10.0);
        
        $this->assertTrue(time() - $account->getCreatedAt()->getTimestamp() < 1000, 'account.createdAt not set with current date');
        
        // add account transaction type
        $in1 = new AccountTransactionTypeIn();
        $in1->setId('in1')->setHasMoreDetails(true);
        
        $in2 = new AccountTransactionTypeIn();
        $in2->setId('in2')->setHasMoreDetails(false);
        
        //add account transaction type
        $out1 = new AccountTransactionTypeOut();
        $out1->setId('out1')->setHasMoreDetails(true);
        
        $out2 = new AccountTransactionTypeOut();
        $out2->setId('out2')->setHasMoreDetails(false);
        
        // add account transaction
        $account->addTransaction(new AccountTransaction($account, $in1, 400.0));
        $account->addTransaction(new AccountTransaction($account, $in2, 150.0));
        
        // add account transaction
        $account->addTransaction(new AccountTransaction($account, $out1, 50.0));
        $account->addTransaction(new AccountTransaction($account, $out2, 30.0));
        
        $this->assertEquals(400.0 + 150.0, $account->getMoneyInTotal());
        $this->assertEquals(50.0 + 30.0, $account->getMoneyOutTotal());
        $this->assertEquals(10.0 + 400.0 + 150.0 - 50.0 - 30.0, $account->getMoneyTotal());
        
        // edit transaction
        $account->findTransactionByTypeId('in1')->setAmount(400.50); //edit (1)
        $this->assertEquals(10.0 + 400.50 + 150.0 - 50.0 - 30.0, $account->getMoneyTotal());
        
    }
}
