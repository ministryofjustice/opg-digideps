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
        $this->client = static::createClient([ 'environment' => 'test',
                                               'debug' => false ]);
        
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @test
     */
    public function addAccount()
    {
        $client = new \AppBundle\Entity\Client;
        $this->em->persist($client);
        
        $cot = new \AppBundle\Entity\CourtOrderType;
        $this->em->persist($cot);
        
        $report = new \AppBundle\Entity\Report;
        $report->setClient($client);
        $report->setCourtOrderType($cot);
        $this->em->persist($report);
        
        $this->em->flush();

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

        $this->assertNull($account->getLastEdit(), 'account.lastEdit must be null on creation');
        
        return $account->getId();
    }
    
    /**
     * @test
     * @depends addAccount
     */
    public function editAccount($accountId)
    {
        $this->client->request(
            'PUT', '/account/'.$accountId,
            array(), array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                // TODO: transactions
            ))
        );
        $response =  $this->client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type','application/json'), 'wrong content type');
        $return = json_decode($response->getContent(), true);
        $this->assertNotEmpty($return, 'Response not json');
        $this->assertTrue($return['success'], $return['message']);
        $this->assertArrayHasKey('message', $return);
        $this->assertTrue($return['data']['id'] > 0);
        
        //TODO: transaction checks
         // assert account created with transactions
        $account = $this->em->getRepository('AppBundle\Entity\Account')->find($return['data']['id']); /* @var $account \AppBundle\Entity\Account */
        $this->assertTrue(time() - $account->getLastEdit()->getTimestamp() < 1000, 'account.lastIed not udpated with current date');
    }
}
