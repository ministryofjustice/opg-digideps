<?php
namespace AppBundle\Resources\views\Account;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Account;
use AppBundle\Entity\Report;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Fixtures;
use Mockery as m;

class AccountContinueTest extends WebTestCase
{
    public function setUp() {
        $this->markTestSkipped('deprecated');
        $client = static::createClient([ 'environment' => 'test',
            'debug' => false]);
        $this->twig = $client->getContainer()->get('templating');
    }

    /** @test */
    public function showContinueWhenThereAreAccounts() {

        $account = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive("getCountValidTotals")->andReturn(1)
            ->getMock();

        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getAccounts')->andReturn([$account])
            ->getMock();


        $html = $this->twig->render('AppBundle:Account:_continue.html.twig', [
            'report' => $report,
            'action' => 'list'
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(1, $crawler->filter('#continue-button'));
        $this->assertEquals("/report/1/assets", $crawler->filter('#continue-button')->eq(0)->attr('href'));

    }

    /** @test */
    public function dontShowContinueWhenThereAreNoAccounts() {

        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getAccounts')->andReturn([])
            ->getMock();


        $html = $this->twig->render('AppBundle:Account:_continue.html.twig', [
            'report' => $report,
            'action' => 'list'
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(0, $crawler->filter('#continue-button'));


    }

    /** @test */
    public function dontShowContinueWhenAddingAccount() {

        $account = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive("getCountValidTotals")->andReturn(1)
            ->getMock();

        
        
        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getAccounts')->andReturn([$account])
            ->getMock();


        $html = $this->twig->render('AppBundle:Account:_continue.html.twig', [
            'report' => $report,
            'action' => 'add'
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(0, $crawler->filter('#continue-button'));


    }


    
}
