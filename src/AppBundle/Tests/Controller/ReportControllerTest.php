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
        $this->client = static::createClient();
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
        $this->em->persist($asset);
        
        $account = new EntityDir\Account();
        $account->setOpeningDate(new \DateTime('01 January 2014'));
        $account->setClosingDate(new \DateTime('31 December 2014'));
        $account->setReport($report);
        $this->em->getRepository('AppBundle:Account')->addEmptyTransactionsToAccount($account);
        $this->em->persist($account);
        
        $this->em->persist($report);
        
        $this->em->flush();
        
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
        
        $newReport = $this->em->getRepository('AppBundle:Report')->find($reportId);
         
        $this->assertEquals($newReport->getStartDate()->format('Y-m-d'),'2015-01-01');
        $this->assertEquals($newReport->getEndDate()->format('Y-m-d'), '2015-12-31');
    }
}

