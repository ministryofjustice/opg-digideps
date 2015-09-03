<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity as EntityDir;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Fixtures;

class ListAccountsDueTest extends WebTestCase
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
        
        //$this->client->getContainer()->enterScope('request');
        //$this->client->getContainer()->set('request', new Request(), 'request');
        
        $this->twig = $this->client->getContainer()->get('templating');
        
        $this->report = Fixtures::createReport(['id'=>1, 'due'=>false]);
        
        $html = $this->twig->render('AppBundle:Account:_listAccounts.html.twig', [
            'report' =>  $this->report,
            'accounts' => [
                Fixtures::createAccount(['id'=>1, 'bank'=>'hsbc bank']), 
                Fixtures::createAccount(['id'=>2, 'bank'=>'halifax bank'])
            ]
        ]);
        
        $crawler = new Crawler($html);
        
        $this->report->setEndDate(new \DateTime('-3 months'));
        
        $html = $this->twig->render('AppBundle:Account:_listAccounts.html.twig', [
            'report' =>  $this->report,
            'accounts' => [
                $this->account1 = Fixtures::createAccount([
                    'id'=>1, 
                    'bank'=>'bank1'
                ]), 
                $this->account2 = Fixtures::createAccount([
                    'id'=>2, 
                    'bank'=>'bank2 with one transaction', 
                    'moneyIn'=>[ 'in1'=> 1 ]
                ]),
                $this->account3 = Fixtures::createAccount([
                    'id'=>3, 
                    'report'=>$this->report,
                    'bank'=> 'bank with one transaction and closing balance', 
                    'moneyIn'=>['in1'=> 1],
                    'closing'=> [
                        'balance' => 1,
                        'balanceExplanation' => 'expl',
                        'date' => new \DateTime(),
                        'dateExplanation' => 'expl'
                    ],
                ]),
            ]
        ]);
        
        // assert the model behaves as expected
        $this->assertTrue($this->account1->needsClosingBalanceData());
        $this->assertTrue($this->account1->getCountValidTotals() === 0);
        
        $this->assertTrue($this->account2->needsClosingBalanceData());
        $this->assertTrue($this->account2->getCountValidTotals() > 0);
        
        $this->assertFalse($this->account3->needsClosingBalanceData());
        
        // crawler and nodes
        $crawler = new Crawler($html);
         
        $this->bank1Node = $crawler->filter('ul.report-list li.report-list__item')->eq(0);
        $this->bank2Node = $crawler->filter('ul.report-list li.report-list__item')->eq(1);
        $this->bank3Node = $crawler->filter('ul.report-list li.report-list__item')->eq(2);
        
    }

    public function testWarningsForAddMoneyClosingBalanceAndNoWarnings()
    {
        // no transaction: expect no money warning
        $this->assertContains('add money', $this->bank1Node->filter('.page-section-warning p')->html(), '', true);

        // 1 transaction: expect add closing balance warning
        $this->assertContains('closing balance', $this->bank2Node->filter('.page-section-warning p')->html(), '', true);
        
        // closing balance added: expect no warnings
        $this->assertCount(0, $this->bank3Node->filter('.page-section-warning'), '', true);
    }
    
}
