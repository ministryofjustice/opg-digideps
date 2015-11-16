<?php
namespace AppBundle\Resources\views\Safeguard;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Mockery as m;

class SafeguardContinueTest extends WebTestCase
{
    public function setUp() {
        $client = static::createClient([ 'environment' => 'test',
            'debug' => false]);
        $this->twig = $client->getContainer()->get('templating');
    }

    /** @test */
    public function showContinueWhenSafeguardingSaved() {

        $safeGuarding = m::mock('AppBundle\Entity\Safeguarding');

        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getSafeguarding')->andReturn($safeGuarding)
            ->getMock();


        $html = $this->twig->render('AppBundle:Safeguard:_continue.html.twig', [
            'report' => $report,
            'action' => 'list'
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(1, $crawler->filter('#continue-button'));
        $this->assertEquals("/report/1/accounts", $crawler->filter('#continue-button')->eq(0)->attr('href'));

    }

    /** @test */
    public function dontShowContinueWhenSafeguardingNotSaved() {

        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getSafeguarding')->andReturn(null)
            ->getMock();


        $html = $this->twig->render('AppBundle:Safeguard:_continue.html.twig', [
            'report' => $report,
            'action' => 'list'
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(0, $crawler->filter('#continue-button'));


    }
}
