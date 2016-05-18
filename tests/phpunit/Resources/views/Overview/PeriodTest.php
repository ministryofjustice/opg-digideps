<?php

namespace AppBundle\Resources\views\Report;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Report as Report;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Mockery as m;

class PeriodTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;
    private $report;
    private $reportStatus;
    private $twig;
    private $todayFormatted;
    private $nextYearformatted;
    private $dueFormatted;

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
    public function confirmDisplaysCorrectDateRange()
    {
        $this->setupReport();

        $html = $this->twig->render('AppBundle:Overview:_period.html.twig', [
            'report' => $this->report,
        ]);

        $crawler = new Crawler($html);

        $dateRangeElement = $crawler->filter('#report-period');

        $expected = $this->todayFormatted.' to '.$this->nextYearformatted;

        $this->assertContains($expected, $dateRangeElement->eq(0)->text());
    }

    /** @test */
    public function confirmDisplaysCorrectDueDate()
    {
        $this->setupReport();

        $html = $this->twig->render('AppBundle:Overview:_period.html.twig', [
            'report' => $this->report,
        ]);

        $crawler = new Crawler($html);

        $dueElement = $crawler->filter('#report-due');

        $this->assertContains($this->dueFormatted, $dueElement->eq(0)->text());
    }

    private function setupReport()
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        $this->todayFormatted = $today->format(' d F Y');

        $nextYear = new \DateTime();
        $nextYear->setTime(0, 0, 0);
        $nextYear->modify('1 year');

        $this->nextYearFormatted = $nextYear->format(' d F Y');

        $dueDate = clone $nextYear;
        $dueDate->modify('+8 weeks');

        $this->dueFormatted = $dueDate->format(' d F Y');

        $this->report = m::mock('AppBundle\Entity\Report')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('getSubmitted')->andReturn(false)
                ->shouldReceive('getId')->andReturn(1)
                ->shouldReceive('isDue')->andReturn(false)
                ->shouldReceive('getStartDate')->andReturn($today)
                ->shouldReceive('getEndDate')->andReturn($nextYear)
                ->shouldReceive('getDueDate')->andReturn($dueDate)
                ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
                ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('getDecisionsState')->andReturn('done')
                ->shouldReceive('getDecisionsStatus')->andReturn('1 Decision')
                ->shouldReceive('getContactsState')->andReturn('done')
                ->shouldReceive('getContactsStatus')->andReturn('1 Contact')
                ->shouldReceive('getSafeguardingState')->andReturn('done')
                ->shouldReceive('getSafeguardingStatus')->andReturn('Complete')
                ->shouldReceive('getAccountsState')->andReturn('done')
                ->shouldReceive('getAccountsStatus')->andReturn('1 Account')
                ->shouldReceive('getAssetsState')->andReturn('done')
                ->shouldReceive('getAssetsStatus')->andReturn('1 Asset')
                ->shouldReceive('isReadyToSubmit')->andReturn(true)
                ->getMock();
    }
}
