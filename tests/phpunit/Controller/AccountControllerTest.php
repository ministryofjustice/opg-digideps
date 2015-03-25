<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccountControllerTest extends WebTestCase
{
    /**
     * @var Symfony\Bundle\FrameworkBundle\Client 
     */
    private $client;
    
    /**
     * @var \Doctrine\ORM\EntityManager 
     */
    private $em;
    
    public function setUp()
    {
        $this->client = static::createClient();
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @test
     */
    public function addAccount()
    {
        // add report
        $report = new \AppBundle\Entity\Report;
        $this->em->persist($report);
        $this->em->flush($report);
        
        // create user
        $this->client->request(
            'POST', '/report/add-account', 
            array(), array(), 
            array('CONTENT_TYPE' => 'application/json'), 
            json_encode(array(
                'report' => $report->getId(),
                'bank' => 'hsbc',
                'sort_code' => '123456',
                'account_number' => '1234',
                'opening_date' => '01/01/2015',
                'opening_balance' => '500',
            ))
        );
        $response =  $this->client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type','application/json'), 'wrong content type');
        $return = json_decode($response->getContent(), true);
        $this->assertNotEmpty($return, 'Response not json');
        $this->assertTrue($return['success'], $return['message']);
        $this->assertArrayHasKey('message', $return);
        $this->assertTrue($return['data']['id'] > 0);
        
        // assert account created with transactions
        $account = $this->em->getRepository('AppBundle\Entity\Account')->find($return['data']['id']); /* @var $account \AppBundle\Entity\Account */
        $transactionTypesTotal = count($this->em->getRepository('AppBundle\Entity\AccountTransactionType')->findAll());
        $this->assertCount($transactionTypesTotal, $account->getTransactions(), "transactions not created");
        
        return $account->getId();
    }
}
