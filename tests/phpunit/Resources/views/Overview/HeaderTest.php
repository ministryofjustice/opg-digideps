<?php

namespace AppBundle\Resources\views\Report;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Report\Report as Report;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Mockery as m;

class HeaderTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;
    private $report;
    private $reportStatus;
    private $twig;

    public function setUp()
    {
        $this->client = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->client->getContainer()->enterScope('request');
        $request = new Request();
        $request->create('/');
        $this->container = $this->client->getContainer();
        $this->container->set('request', $request, 'request');
        $this->twig = $this->client->getContainer()->get('templating');
        $this->container->get('request_stack')->push(Request::createFromGlobals());
    }

    public function tearDown()
    {
        m::close();
        $this->container->leaveScope('request');
    }

    /** @test */
    public function reportDueAllSectionsCompletedShowSubmission()
    {
        $this->report = m::mock('AppBundle\Entity\Report\Report')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isDue')->andReturn(true)
                ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isReadyToSubmit')->andReturn(true)
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_header.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);

        // Has a head submit element
        $headerSubmitElement = $crawler->filter('#header-report-submit');
        $this->assertEquals(1, $headerSubmitElement->count());

        // The header has a link that links to the report submission
        $submitLink = $headerSubmitElement->filter('.submit-link');
        $this->assertEquals(1, $submitLink->count());

        $link = $submitLink->eq(0);
        $url = $link->attr('href');

        $this->assertContains('/report/1/review', $url);
    }

    /** @test */
    public function reportNotDueAllSectionsCompleteDontShowSubmission()
    {
        $this->report = m::mock('AppBundle\Entity\Report\Report')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isDue')->andReturn(false)
                ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isReadyToSubmit')->andReturn(true)
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_header.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);

        $headerSubmitElement = $crawler->filter('#page-report-header #header-report-submit');
        $this->assertEquals(0, $headerSubmitElement->count());
    }

    /** @test */
    public function reportDueAllSectionsNotCompleteDontShowSubmission()
    {
        $this->report = m::mock('AppBundle\Entity\Report\Report')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isDue')->andReturn(false)
                ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isReadyToSubmit')->andReturn(true)
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_header.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);

        $headerSubmitElement = $crawler->filter('#page-report-header #header-report-submit');
        $this->assertEquals(0, $headerSubmitElement->count());
    }

    /** @test */
    public function reportDueAllSectionsCompleteDontShowNews()
    {
        $this->report = m::mock('AppBundle\Entity\Report\Report')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isDue')->andReturn(true)
                ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isReadyToSubmit')->andReturn(true)
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_header.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
            'app' => $this->getApp(),
        ]);

        $crawler = new Crawler($html);

        // Has a head submit element
        $newsElement = $crawler->filter('#page-report-header .flash-news');
        $this->assertEquals(0, $newsElement->count());
    }

    private function getApp()
    {
        $flashbag = m::mock('Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('get')->with('news')->andReturn(['test'])
                ->getMock();

        $session = m::mock('Symfony\Component\HttpFoundation\Session\Session')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('getFlashBag')->andReturn($flashbag)
                ->getMock();

        $app = m::mock('Symfony\Bundle\FrameworkBundle\Templating\GlobalVariables')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('getSession')->andReturn($session)
                ->getMock();

        return $app;
    }
}
