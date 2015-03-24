<?php
namespace AppBundle\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccountTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager 
     */
    private $em;

    protected function setUp()
    {
        $this->client = static::createClient();
        
        
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }

    public function testSetterGetters()
    {
        $account = new Account;
        
        $this->assertEquals('123456', $account->setAccountNumber('123456')->getAccountNumber());
        $this->assertEquals('123456', $account->setBalanceJustification('123456')->getBalanceJustification());
        $this->assertEquals('123456', $account->setBank('123456')->getBank());
        $this->assertEquals('123456', $account->setClosingBalance('123456')->getClosingBalance());
        $this->assertEquals('123456', $account->setOpeningBalance('123456')->getOpeningBalance());
    }
    
    public function testTotals()
    {
        $account = new Account();
        $account->setOpeningBalance(10.0);
        
        $this->em->getRepository('AppBundle\Entity\Account')->addEmptyTransactionsToAccount($account);
        
        $this->em->persist($account);
        $this->em->flush($account);
        
        //edit money In
        $account->findTransactionByTypeId('attendance_allowance')->setAmount(400.0);
        $account->findTransactionByTypeId('state_pension')->setAmount(150.0);
        
        // edit money out
        $account->findTransactionByTypeId('tax_payable_to_hmrc')->setAmount(50.0);
        $account->findTransactionByTypeId('gifts')->setAmount(30.0);
        
        $this->em->flush();
        
        $this->assertEquals(400.0 + 150.0, $account->getMoneyInTotal());
        $this->assertEquals(50.0 + 30.0, $account->getMoneyOutTotal());
        $this->assertEquals(10.0 + 400.0 + 150.0 - 50.0 - 30.0, $account->getMoneyTotal());
    }
}
