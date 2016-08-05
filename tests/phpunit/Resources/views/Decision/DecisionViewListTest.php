<?php

namespace AppBundle\Resources\views\Decision;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DomCrawler\Crawler;
use Mockery as m;

class DecisionViewListTest extends WebTestCase
{
    /** @var  \Symfony\Bundle\TwigBundle\TwigEngine */
    private $twig;

    /** @var  \Symfony\Bundle\FrameworkBundle\ContainerInterface */
    private $container;

    public function setUp()
    {
        $client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->container = $client->getContainer();

        $this->twig = $this->container->get('templating');

        $request = new Request();
        $request->create('/report/1/decisions');
        $this->container->enterScope('request');
        $this->container->set('request', $request, 'request');
        $this->container->get('request_stack')->push(Request::createFromGlobals());
    }

    protected function tearDown()
    {
        m::close();
        $this->container->leaveScope('request');
    }

    // Continue Button

    /** @test */
    public function showNextWhenThereAreDecisions()
    {
        $decision = $this->getMockDecision();

        // mock data
        $report = m::mock('AppBundle\Entity\Report\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getDecisions')->andReturn([$decision])
            ->getMock();

        $html = $this->twig->render('AppBundle:Report/Decision:list.html.twig', [
            'report' => $report,
            'decisions' => [$decision],
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(1, $crawler->filter('nav.pagination .next'));
        $this->assertEquals('/report/1/mental-capacity', $crawler->filter('nav.pagination .next a')->eq(0)->attr('href'));
        $this->assertEquals('Mental capacity', $crawler->filter('nav.pagination .next .pagination-part-title')->eq(0)->text());
    }

    /** @test */
    public function showNextWhenNoDecisionAndReason()
    {

        // mock data
        $report = m::mock('AppBundle\Entity\Report\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getDecisions')->andReturn([])
            ->shouldReceive('getReasonForNoDecisions')->andReturn('nothing')
            ->getMock();

        $html = $this->twig->render('AppBundle:Report/Decision:list.html.twig', [
            'report' => $report,
            'decisions' => [],
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(1, $crawler->filter('nav.pagination .next'));
        $this->assertEquals('/report/1/mental-capacity', $crawler->filter('nav.pagination .next a')->eq(0)->attr('href'));
        $this->assertEquals('Mental capacity', $crawler->filter('nav.pagination .next .pagination-part-title')->eq(0)->text());
    }

    /** @test */
    /*public function dontShowNextWhenNoDecisionsNoReasonAndDue() {

        // mock data
        $report = m::mock('AppBundle\Entity\Report\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(true)
            ->shouldReceive('getDecisions')->andReturn([])
            ->shouldReceive('getReasonForNoDecisions')->andReturn("")
            ->getMock();


        $html = $this->twig->render('AppBundle:Report/Decision:list.html.twig', [
            'report' => $report,
            'decisions' => []
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(0, $crawler->filter('nav.pagination .next'));
    }
    */

    // Show List or Add

    /** @test */
    public function showDecisionsWhenDecisions()
    {
        $client = m::mock('AppBundle\Entity\Client')
            ->shouldIgnoreMissing(true)
            ->getMock();

        $decision = $this->getMockDecision();

        // mock data
        $report = m::mock('AppBundle\Entity\Report\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getDecisions')->andReturn([$decision])
            ->getMock();

        $html = $this->twig->render('AppBundle:Report/Decision:list.html.twig', [
            'report' => $report,
            'client' => $client,
            'decisions' => [$decision],
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(1, $crawler->filter('#decision-list'));
        $this->assertCount(1, $crawler->filter('#decision-list li'));
    }

    /** @test */
    public function showsAddButton()
    {
        $decision = $this->getMockDecision();

        // mock data
        $report = m::mock('AppBundle\Entity\Report\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getDecisions')->andReturn([$decision])
            ->getMock();

        $html = $this->twig->render('AppBundle:Report/Decision:list.html.twig', [
            'report' => $report,
            'decisions' => [$decision],
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(1, $crawler->filter('.button-bar-add a'));
    }

    /** @test */
    public function dontShowListWhenNoDecisions()
    {

        // mock data
        $report = m::mock('AppBundle\Entity\Report\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(true)
            ->shouldReceive('getDecisions')->andReturn([])
            ->getMock();

        $html = $this->twig->render('AppBundle:Report/Decision:list.html.twig', [
            'report' => $report,
            'decisions' => [],
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(0, $crawler->filter('#decision-list'));
    }

    // Show reason for none

    /** @test */
    public function listActionEmbedReasonFormWhenNoReasonAndDue()
    {
        // mock data
        $report = m::mock('AppBundle\Entity\Report\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(true)
            ->shouldReceive('getDecisions')->andReturn([])
            ->shouldReceive('getReasonForNoDecisions')->andReturn('')
            ->getMock();

        $html = $this->twig->render('AppBundle:Report/Decision:list.html.twig', [
            'report' => $report,
            'decisions' => [],
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(1, $crawler->filter('#no-decision-reason-form-embed'));
        $this->assertCount(0, $crawler->filter('#no-decision-reason-description'));
    }

    /** @test */
    public function showReasonDescriptionWhenReason()
    {
        // mock data
        $report = m::mock('AppBundle\Entity\Report\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(true)
            ->shouldReceive('getDecisions')->andReturn([])
            ->shouldReceive('getReasonForNoDecisions')->andReturn('some reason')
            ->getMock();

        $html = $this->twig->render('AppBundle:Report/Decision:list.html.twig', [
            'report' => $report,
            'decisions' => [],
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(0, $crawler->filter('#no-decision-reason-form-embed'));
        $this->assertCount(1, $crawler->filter('#no-decision-reason-description'));
    }

    private function getMockDecision()
    {
        $decision = m::mock('AppBundle\Entity\Report\Decision')
            ->shouldIgnoreMissing()
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('getDescription')->andReturn('abcd')
            ->shouldReceive('getClientInvolvedBoolean')->andReturn(true)
            ->shouldReceive('getClientInvolvedDetailed')->andReturn('dcba')
            ->getMock();

        return $decision;
    }
}
