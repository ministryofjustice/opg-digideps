<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Account;
use AppBundle\Entity\Report;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Fixtures;
use Mockery as m;

class ListAccountsDueTest extends WebTestCase
{
    public function setUp()
    {
        $this->markTestSkipped('deprecated');
        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(true)
            ->getMock();

        $account1 = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing()
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('needsClosingBalanceData')->atLeast(1)->andReturn(true)
            ->shouldReceive('getCountValidTotals')->andReturn(0)
            ->getMock();
        
        $account2 = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing()
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('needsClosingBalanceData')->atLeast(1)->andReturn(true)
            ->shouldReceive('getCountValidTotals')->andReturn(1)
            ->getMock();
        
        $account3 = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing()
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('needsClosingBalanceData')->atLeast(1)->andReturn(false)
            ->getMock();

        // create WebClient and Crawler
        $client = static::createClient([ 'environment' => 'test',
                'debug' => false]);

        
        $this->twig = $client->getContainer()->get('templating');
        
        $html = $this->twig->render('AppBundle:Account:_listAccounts.html.twig', [
            'report' => $report,
            'accounts' => [
                $account1,
                $account2,
                $account3,
            ]
        ]);

        $crawler = new Crawler($html);
         
        // prepare html nodes used for testing
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

    public function tearDown()
    {
        m::close();
    }

}