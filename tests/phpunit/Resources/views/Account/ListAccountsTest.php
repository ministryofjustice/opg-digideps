<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Account;
use AppBundle\Entity\Report;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Fixtures;
use Mockery as m;

class ListAccountsTest extends WebTestCase
{
    public function setUp()
    {
        $this->markTestSkipped('deprecated');
        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->getMock();

        $account1 = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing()
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('getBank')->andReturn('hsbc bank')
            ->shouldReceive('needsClosingBalanceData')->atLeast(1)->andReturn(false)
            ->shouldReceive('getCountValidTotals')->andReturn(0)
            ->getMock();
        
        $account2 = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing()
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('getBank')->andReturn('halifax bank')
            ->shouldReceive('needsClosingBalanceData')->atLeast(1)->andReturn(false)
            ->shouldReceive('getCountValidTotals')->andReturn(0)
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
            ]
        ]);

        $crawler = new Crawler($html);
         
        // prepare html nodes used for testing
        $this->hsbcNode = $crawler->filter('ul.report-list li.report-list__item')->eq(0);
        $this->halifaxNode = $crawler->filter('ul.report-list li.report-list__item')->eq(1);
    }


    public function testBankNamesAreDisplayed()
    {
        $this->assertEquals('hsbc bank', trim($this->hsbcNode->filter('dd.report-list__item-fields-description')->text(), "\n "));
        $this->assertEquals('halifax bank', trim($this->halifaxNode->filter('dd.report-list__item-fields-description')->text(), "\n "));
    }
    
    public function testWarningsAreNotDisplayed()
    {
        $this->assertCount(0, $this->hsbcNode->filter('.page-section-warning'));
        $this->assertCount(0, $this->halifaxNode->filter('.page-section-warning'));
    }
    
    
    public function tearDown()
    {
        m::close();
    }

}