<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity as EntityDir;

class ReportControllerTest extends WebTestCase
{
    private $client;
    private $em;
    
    public function setUp()
    {
        $this->client = static::createClient([ 'environment' => 'test', 
                                               'debug' => false ]);
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }
    
    public function testCloneAction()
    {
        $client = new EntityDir\Client();
        $this->em->persist($client);
        
        $courtOrderType = new EntityDir\CourtOrderType();
        $this->em->persist($courtOrderType);
        
        $report = new EntityDir\Report();
        $report->setClient($client);
        $report->setCourtOrderType($courtOrderType);
        $report->setStartDate(new \DateTime('01 January 2014'));
        $report->setEndDate(new \DateTime('31 December 2014'));
        
        $asset = new EntityDir\Asset();
        $asset->setReport($report);
        $asset->setTitle('test');
        $asset->setDescription('test');
        $asset->setValue(100);
        $asset->setValuationDate(new \DateTime('10 June 2013'));
        $this->em->persist($asset);
        
        $account = new EntityDir\Account();
        $account->setOpeningDate(new \DateTime('01 January 2014'));
        $account->setClosingDate(new \DateTime('31 December 2014'));
        $account->setReport($report);
        $account->setBank('NATWEST');
        $account->setSortCode('120044');
        $account->setAccountNumber('0012');
        $account->setCreatedAt(new \DateTime());

        $this->em->getRepository('AppBundle:Account')->addEmptyTransactionsToAccount($account);
        $this->em->persist($account);
        
        $this->em->persist($report);

        $this->em->flush();
        
        $this->em->clear();

        $this->client->request(
            'POST', '/report/clone',
            array(), array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'id' => $report->getId()
            ))
        );
        
        $responseArray = json_decode($this->client->getResponse()->getContent(),true);
        
        $reportId = $responseArray['data']['report'];
    
        $this->em->clear();

        $newReport = $this->em->getRepository('AppBundle:Report')->find($reportId);
    
        $this->assertEquals($newReport->getStartDate()->format('Y-m-d'),'2015-01-01');
        $this->assertEquals($newReport->getEndDate()->format('Y-m-d'), '2015-12-31');
        $this->assertCount(1, $newReport->getAssets());

        $assert = $newReport->getAssets()[0];

        $this->assertEquals('test', $asset->getTitle());
        $this->assertEquals('test', $asset->getDescription());
        $this->assertEquals(100, $asset->getValue());
        $this->assertEquals('2013-06-10', $asset->getValuationDate()->format('Y-m-d'));

        $this->assertCount(1, $newReport->getAccounts());

        $account = $newReport->getAccounts()[0];

        $this->assertEquals('2014-12-31', $account->getOpeningDate()->format('Y-m-d'));
        $this->assertEquals('NATWEST', $account->getBank());
        $this->assertEquals('120044', $account->getSortCode());
        $this->assertEquals('0012', $account->getAccountNumber());
        $this->assertCount(40,$account->getTransactions());
    }
}

