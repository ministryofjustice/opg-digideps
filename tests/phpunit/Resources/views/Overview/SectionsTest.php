<?php

namespace AppBundle\Resources\views\Report;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Report\Report as Report;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Mockery as m;

class SectionsTest extends WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;
    private $report;
    private $reportClient;
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
    public function decisionsSectionDescription()
    {
        $this->setupReport();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);
        $descriptionElement = $crawler->filter('#decisions-sub-section .description');

        $this->assertEquals(1, $descriptionElement->count());

        $this->assertContains("Add the significant decisions you've made for Fred", $descriptionElement->eq(0)->text());
    }

    /** @test */
    public function contactsSectionDescription()
    {
        $this->setupReport();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);
        $descriptionElement = $crawler->filter('#contacts-sub-section .description');

        $this->assertEquals(1, $descriptionElement->count());

        $this->assertContains('Give us the details of people you consulted about Fred', $descriptionElement->eq(0)->text());
    }

    /** @test */
    public function visitsCareSectionDescription()
    {
        $this->setupReport();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);
        $descriptionElement = $crawler->filter('#safeguarding-sub-section .description');

        $this->assertEquals(1, $descriptionElement->count());

        $this->assertContains("We need to know about your involvement with Fred's care", $descriptionElement->eq(0)->text());
    }

    /** @test */
    public function accountsSectionDescription()
    {
        $this->setupReport();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);
        $descriptionElement = $crawler->filter('#accounts-sub-section .description');

        $this->assertEquals(1, $descriptionElement->count());

        $this->assertContains('Add details of all income and spending in the reporting period', $descriptionElement->eq(0)->text());
    }

    /** @test */
    public function assetsSectionDescription()
    {
        $this->setupReport();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);
        $descriptionElement = $crawler->filter('#assets-sub-section .description');

        $this->assertEquals(1, $descriptionElement->count());

        $this->assertContains("Add Fred's property, investments and other valuables", $descriptionElement->eq(0)->text());
    }

    /** @test */
    public function whenAReportIsDueAndAllSectionsCompletedAllowSubmission()
    {
        $this->report = m::mock('AppBundle\Entity\Report\Report')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isDue')->andReturn(true)
                ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isReadyToSubmit')->andReturn(true)
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);

        $submitReportLinkElement = $crawler->filter('#edit-report_add_further_info');
        $this->assertEquals(1, $submitReportLinkElement->count());
    }

    /** @test */
    public function whenAReportIsNotDueAndAllSectionsCompletedDontAllowSubmission()
    {
        $this->report = m::mock('AppBundle\Entity\Report\Report')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isDue')->andReturn(false)
                ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isReadyToSubmit')->andReturn(true)
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);

        $submitReportLinkElement = $crawler->filter('#edit-report_add_further_info');
        $this->assertEquals(0, $submitReportLinkElement->count());
    }

    /** @test */
    public function whenAReportIsDueAndAllSectionsCompletedIndicateActive()
    {
        $this->report = m::mock('AppBundle\Entity\Report\Report')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isDue')->andReturn(true)
                ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isReadyToSubmit')->andReturn(true)
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);

        $submitReportLinkElement = $crawler->filter('#report-submit-section.inactive');
        $this->assertEquals(0, $submitReportLinkElement->count());
    }

    /** @test */
    public function whenAReportIsNotDueAndAllSectionsCompletedIndicateInactive()
    {
        $this->report = m::mock('AppBundle\Entity\Report\Report')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isDue')->andReturn(false)
                ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isReadyToSubmit')->andReturn(true)
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);

        $submitReportLinkElement = $crawler->filter('#report-submit-section .disabled');
        $this->assertEquals(1, $submitReportLinkElement->count());
    }

    /** @test */
    public function whenAReportIsNotDueAndAllSectionsAreNotCompletedIndicateInactive()
    {
        $this->report = m::mock('AppBundle\Entity\Report\Report')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isDue')->andReturn(false)
                ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isReadyToSubmit')->andReturn(false)
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);

        $submitReportLinkElement = $crawler->filter('#report-submit-section .disabled');
        $this->assertEquals(1, $submitReportLinkElement->count());
    }

    /** @test */
    public function showSubmitWarningIsNotDueButReady()
    {
        $tomorrow = new \DateTime();
        $tomorrow->setTime(0, 0, 0);
        $tomorrow->modify('1 day');

        $formatted = $tomorrow->format(' d F Y');

        $this->report = m::mock('AppBundle\Entity\Report\Report')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isDue')->andReturn(false)
                ->shouldReceive('getEndDate')->andReturn($tomorrow)
                ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isReadyToSubmit')->andReturn(true)
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);

        $submitReportLinkElement = $crawler->filter('#cannot-submit-warning');
        $this->assertEquals(1, $submitReportLinkElement->count());

        $this->assertContains("You can't submit your report until ".$formatted, $submitReportLinkElement->eq(0)->text());
    }

    /** @test */
    public function showSubmitWarningIsDueButNotReady()
    {
        $this->report = m::mock('AppBundle\Entity\Report\Report')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isDue')->andReturn(true)
                ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isReadyToSubmit')->andReturn(false)
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);

        $submitReportLinkElement = $crawler->filter('#cannot-submit-warning');
        $this->assertEquals(1, $submitReportLinkElement->count());
        $this->assertContains("You can't submit your report until the report is complete", $submitReportLinkElement->eq(0)->text());
    }

    /** @test */
    public function showSubmitWarningIsNotDueAndNotReady()
    {
        $tomorrow = new \DateTime();
        $tomorrow->setTime(0, 0, 0);
        $tomorrow->modify('1 day');

        $formatted = $tomorrow->format(' d F Y');

        $this->report = m::mock('AppBundle\Entity\Report\Report')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isDue')->andReturn(false)
                ->shouldReceive('getEndDate')->andReturn($tomorrow)
                ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isReadyToSubmit')->andReturn(false)
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);

        $submitReportLinkElement = $crawler->filter('#cannot-submit-warning');
        $this->assertEquals(1, $submitReportLinkElement->count());

        $this->assertContains("You can't submit your report until ".$formatted, $submitReportLinkElement->eq(0)->text());
    }

    /** @test */
    public function dontShowSubmitWarningIsDueAndReady()
    {
        $this->report = m::mock('AppBundle\Entity\Report\Report')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isDue')->andReturn(true)
                ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('isReadyToSubmit')->andReturn(true)
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);

        $submitReportLinkElement = $crawler->filter('#cannot-submit-warning');
        $this->assertEquals(0, $submitReportLinkElement->count());
    }

    /** @test */
    public function showStartWhenThenAreNoDecisionsOrReason()
    {
        $this->setupReport();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('getDecisionsState')->andReturn('not-started')
                ->shouldReceive('getDecisionsStatus')->andReturn('0 Decisions')
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);
        $linkElement = $crawler->filter('#decisions-sub-section .edit-link');

        $this->assertContains('Start decisions', $linkElement->eq(0)->text());
    }

    /** @test */
    public function showEditWhenThenAreDecisions()
    {
        $this->setupReport();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('getDecisionsState')->andReturn('done')
                ->shouldReceive('getDecisionsStatus')->andReturn('1 Decision')
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);
        $linkElement = $crawler->filter('#decisions-sub-section .edit-link');

        $this->assertContains('Edit decisions', $linkElement->eq(0)->text());
    }

    /** @test */
    public function showStartWhenThenAreNoContactsOrReason()
    {
        $this->setupReport();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('getContactsState')->andReturn('not-started')
                ->shouldReceive('getContactsStatus')->andReturn('0 Contacts')
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);
        $linkElement = $crawler->filter('#contacts-sub-section .edit-link');

        $this->assertContains('Start contacts', $linkElement->eq(0)->text());
    }

    /** @test */
    public function showEditWhenThenAreContacts()
    {
        $this->setupReport();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('getContactsState')->andReturn('done')
                ->shouldReceive('getContactsStatus')->andReturn('1 Contact')
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);
        $linkElement = $crawler->filter('#contacts-sub-section .edit-link');

        $this->assertContains('Edit contacts', $linkElement->eq(0)->text());
    }

    /** @test */
    public function showStartWhenThenIsNoVisitsCareData()
    {
        $this->setupReport();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('getVisitsCareStatus')->andReturn('notstarted')
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);
        $linkElement = $crawler->filter('#safeguarding-sub-section .edit-link');

        $this->assertContains('Start visits', $linkElement->eq(0)->text());
    }

    /** @test */
    public function showEditWhenThenIsVisitsCareData()
    {
        $this->setupReport();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('getVisitsCareState')->andReturn('done')
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);
        $linkElement = $crawler->filter('#safeguarding-sub-section .edit-link');

        $this->assertContains('Edit visits', $linkElement->eq(0)->text());
    }

    /** @test */
    public function showStartWhenThenAreNoAccounts()
    {
        $this->setupReport();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('getAccountsState')->andReturn('not-started')
                ->shouldReceive('getAccountsStatus')->andReturn('0 Accounts')
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);
        $linkElement = $crawler->filter('#accounts-sub-section .edit-link');

        $this->assertContains('Start accounts', $linkElement->eq(0)->text());
    }

    /** @test */
    public function showEditWhenThenAreAccountsMidEdit()
    {
        $this->setupReport();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('getAccountsState')->andReturn('incomplete')
                ->shouldReceive('getAccountsStatus')->andReturn('1 Accounts')
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);
        $linkElement = $crawler->filter('#accounts-sub-section .edit-link');

        $this->assertContains('Edit accounts', $linkElement->eq(0)->text());
    }

    /** @test */
    public function showEditWhenThenAreAccounts()
    {
        $this->setupReport();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('getAccountsState')->andReturn('done')
                ->shouldReceive('getAccountsStatus')->andReturn('1 Accounts')
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);
        $linkElement = $crawler->filter('#accounts-sub-section .edit-link');

        $this->assertContains('Edit accounts', $linkElement->eq(0)->text());
    }

    /** @test */
    public function showStartWhenThenAreNoAssets()
    {
        $this->setupReport();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('getAssetsState')->andReturn('not-started')
                ->shouldReceive('getAssetsStatus')->andReturn('0 Assets')
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);
        $linkElement = $crawler->filter('#assets-sub-section .edit-link');

        $this->assertContains('Start assets', $linkElement->eq(0)->text());
    }

    /** @test */
    public function showStartWhenThenAreAssets()
    {
        $this->setupReport();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('getAssetsState')->andReturn('done')
                ->shouldReceive('getAssetsStatus')->andReturn('1 Assets')
                ->getMock();

        $html = $this->twig->render('AppBundle:Report/Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
        ]);

        $crawler = new Crawler($html);
        $linkElement = $crawler->filter('#assets-sub-section .edit-link');

        $this->assertContains('Edit assets', $linkElement->eq(0)->text());
    }

    private function setupReport()
    {
        $this->reportClient = m::mock('AppBundle\Entity\Client')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('getFullname')->andReturn('Fred Smith')
                ->shouldReceive('getFirstname')->andReturn('Fred')
                ->getMock();

        $this->report = m::mock('AppBundle\Entity\Report\Report')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('getSubmitted')->andReturn(false)
                ->shouldReceive('getId')->andReturn(1)
                ->shouldReceive('isDue')->andReturn(true)
                ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
                ->shouldReceive('getClient')->andReturn($this->reportClient)
                ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
                ->shouldIgnoreMissing(true)
                ->shouldReceive('getDecisionsState')->andReturn('done')
                ->shouldReceive('getDecisionsStatus')->andReturn('1 Decision')
                ->shouldReceive('getContactsState')->andReturn('done')
                ->shouldReceive('getContactsStatus')->andReturn('1 Contact')
                ->shouldReceive('getVisitsCareState')->andReturn('done')
                ->shouldReceive('getVisitsCareStatus')->andReturn('Complete')
                ->shouldReceive('getAccountsState')->andReturn('done')
                ->shouldReceive('getAccountsStatus')->andReturn('1 Account')
                ->shouldReceive('getAssetsState')->andReturn('done')
                ->shouldReceive('getAssetsStatus')->andReturn('1 Asset')
                ->shouldReceive('isReadyToSubmit')->andReturn(true)
                ->getMock();
    }
}
