<?php
namespace phpunit\AppBundle\Resources\views\Report;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Report as Report;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Mockery as m;

class SectionsTest extends WebTestCase
{

    /**
     * @var Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;
    
    private $report;
    private $reportClient;
    
    private $reportStatus;
    
    private $twig;

    public function setUp()
    {
        $this->client = static::createClient([ 'environment' => 'test','debug' => false ]);
        $this->client->getContainer()->enterScope('request');
        $request = new Request();
        $request->create('/');
        $this->client->getContainer()->set('request', $request, 'request');
        $this->twig = $this->client->getContainer()->get('templating');
    }
    public function tearDown()
    {
        m::close();
    }

    /** @test */
    public function decisionsSectionDescription()
    {
        $this->setupReport();
        
        $html = $this->twig->render('AppBundle:Overview:_sections.html.twig', [
           'report' => $this->report,
            'reportStatus' => $this->reportStatus,
            'client' => $this->reportClient
        ]);
        
        $crawler = new Crawler($html);
        $descriptionElement = $crawler->filter('#decisions-sub-section .sub-section-description');
       
        $this->assertEquals(1, $descriptionElement->count());


        $this->assertContains("Add the significant decisions you've made for Fred Smith", $descriptionElement->eq(0)->text());

    }

    /** @test */
    public function contactsSectionDescription()
    {
        $this->setupReport();

        $html = $this->twig->render('AppBundle:Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
            'client' => $this->reportClient
        ]);

        $crawler = new Crawler($html);
        $descriptionElement = $crawler->filter('#contacts-sub-section .sub-section-description');

        $this->assertEquals(1, $descriptionElement->count());


        $this->assertContains("Give us the details of people you consulted about Fred Smith", $descriptionElement->eq(0)->text());

    }

    /** @test */
    public function safeguardingSectionDescription()
    {
        $this->setupReport();

        $html = $this->twig->render('AppBundle:Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
            'client' => $this->reportClient
        ]);

        $crawler = new Crawler($html);
        $descriptionElement = $crawler->filter('#safeguarding-sub-section .sub-section-description');

        $this->assertEquals(1, $descriptionElement->count());


        $this->assertContains("We need to know about your involvement with Fred Smith, and their care", $descriptionElement->eq(0)->text());

    }

    /** @test */
    public function accountsSectionDescription()
    {
        $this->setupReport();

        $html = $this->twig->render('AppBundle:Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus
        ]);

        $crawler = new Crawler($html);
        $descriptionElement = $crawler->filter('#accounts-sub-section .sub-section-description');

        $this->assertEquals(1, $descriptionElement->count());


        $this->assertContains("Add details of all income and spending in the reporting period", $descriptionElement->eq(0)->text());

    }

    /** @test */
    public function assetsSectionDescription()
    {
        $this->setupReport();

        $html = $this->twig->render('AppBundle:Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus,
            'client' => $this->reportClient
        ]);

        $crawler = new Crawler($html);
        $descriptionElement = $crawler->filter('#assets-sub-section .sub-section-description');

        $this->assertEquals(1, $descriptionElement->count());


        $this->assertContains("Add Fred Smith's property, investments and other valuables", $descriptionElement->eq(0)->text());

    }

    /** @test */
    public function whenAReportIsDueAndAllSectionsCompletedAllowSubmission() {

        $this->report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isDue')->andReturn(true)
            ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isReadyToSubmit')->andReturn(true)
            ->getMock();

        $html = $this->twig->render('AppBundle:Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus
        ]);

        $crawler = new Crawler($html);
        
        $submitReportLinkElement = $crawler->filter('#report-submit-section a');
        $this->assertEquals(2, $submitReportLinkElement->count());
    }

    /** @test */
    public function whenAReportIsNotDueAndAllSectionsCompletedDontAllowSubmission() {
        $this->report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isDue')->andReturn(false)
            ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isReadyToSubmit')->andReturn(true)
            ->getMock();

        $html = $this->twig->render('AppBundle:Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus
        ]);

        $crawler = new Crawler($html);

        $submitReportLinkElement = $crawler->filter('#report-submit-section a');
        $this->assertEquals(0, $submitReportLinkElement->count());       
    }

    /** @test */
    public function whenAReportIsDueAndAllSectionsCompletedIndicateActive() {
        $this->report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isDue')->andReturn(true)
            ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isReadyToSubmit')->andReturn(true)
            ->getMock();

        $html = $this->twig->render('AppBundle:Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus
        ]);

        $crawler = new Crawler($html);

        $submitReportLinkElement = $crawler->filter('#report-submit-section.inactive');
        $this->assertEquals(0, $submitReportLinkElement->count());    
    }

    /** @test */
    public function whenAReportIsDueAndAllSectionsAreNotCompletedIndicateInactive() {
        $this->report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isDue')->andReturn(true)
            ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isReadyToSubmit')->andReturn(false)
            ->getMock();

        $html = $this->twig->render('AppBundle:Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus
        ]);

        $crawler = new Crawler($html);

        $submitReportLinkElement = $crawler->filter('#report-submit-section.inactive');
        $this->assertEquals(1, $submitReportLinkElement->count());
    }

    /** @test */
    public function whenAReportIsNotDueAndAllSectionsCompletedIndicateInactive() {
        $this->report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isDue')->andReturn(false)
            ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isReadyToSubmit')->andReturn(true)
            ->getMock();

        $html = $this->twig->render('AppBundle:Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus
        ]);

        $crawler = new Crawler($html);

        $submitReportLinkElement = $crawler->filter('#report-submit-section.inactive');
        $this->assertEquals(1, $submitReportLinkElement->count());
    }

    /** @test */
    public function whenAReportIsNotDueAndAllSectionsAreNotCompletedIndicateInactive() {
        $this->report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isDue')->andReturn(false)
            ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isReadyToSubmit')->andReturn(false)
            ->getMock();

        $html = $this->twig->render('AppBundle:Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus
        ]);

        $crawler = new Crawler($html);

        $submitReportLinkElement = $crawler->filter('#report-submit-section.inactive');
        $this->assertEquals(1, $submitReportLinkElement->count());
    }

    /** @test */
    public function showSubmitWarningIsNotDueButReady() {

        $tomorrow = new \DateTime;
        $tomorrow->setTime(0, 0, 0);
        $tomorrow->modify('1 day'); 
        
        $formatted = $tomorrow->format(" d F Y");
        
        $this->report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getEndDate')->andReturn($tomorrow)
            ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isReadyToSubmit')->andReturn(true)
            ->getMock();

        $html = $this->twig->render('AppBundle:Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus
        ]);

        $crawler = new Crawler($html);

        $submitReportLinkElement = $crawler->filter('#cannot-submit-warning');
        $this->assertEquals(1, $submitReportLinkElement->count());
        
        $this->assertContains("You can't submit your report until " . $formatted, $submitReportLinkElement->eq(0)->text());
    }

    /** @test */
    public function showSubmitWarningIsDueButNotReady() {
        $this->report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isDue')->andReturn(true)
            ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isReadyToSubmit')->andReturn(false)
            ->getMock();

        $html = $this->twig->render('AppBundle:Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus
        ]);

        $crawler = new Crawler($html);

        $submitReportLinkElement = $crawler->filter('#cannot-submit-warning');
        $this->assertEquals(1, $submitReportLinkElement->count());
        $this->assertContains("You can't submit your report until the report is complete", $submitReportLinkElement->eq(0)->text());
    }

    /** @test */
    public function showSubmitWarningIsNotDueAndNotReady() {
        $tomorrow = new \DateTime;
        $tomorrow->setTime(0, 0, 0);
        $tomorrow->modify('1 day');

        $formatted = $tomorrow->format(" d F Y");

        
        $this->report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getEndDate')->andReturn($tomorrow)
            ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isReadyToSubmit')->andReturn(false)
            ->getMock();

        $html = $this->twig->render('AppBundle:Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus
        ]);

        $crawler = new Crawler($html);

        $submitReportLinkElement = $crawler->filter('#cannot-submit-warning');
        $this->assertEquals(1, $submitReportLinkElement->count());

        $this->assertContains("You can't submit your report until " . $formatted, $submitReportLinkElement->eq(0)->text());

    }

    /** @test */
    public function dontShowSubmitWarningIsDueAndReady() {
        $this->report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isDue')->andReturn(true)
            ->getMock();

        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('isReadyToSubmit')->andReturn(true)
            ->getMock();

        $html = $this->twig->render('AppBundle:Overview:_sections.html.twig', [
            'report' => $this->report,
            'reportStatus' => $this->reportStatus
        ]);

        $crawler = new Crawler($html);

        $submitReportLinkElement = $crawler->filter('#cannot-submit-warning');
        $this->assertEquals(0, $submitReportLinkElement->count());
    }
    
    
    private function setupReport() 
    {
        $this->report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getSubmitted')->andReturn(false)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(true)
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->getMock();
        
        $this->reportStatus = m::mock('AppBundle\Service\ReportStatusService')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getDecisionsState')->andReturn("done")
            ->shouldReceive('getDecisionsStatus')->andReturn("1 Decision")
            ->shouldReceive('getContactsState')->andReturn("done")
            ->shouldReceive('getContactsStatus')->andReturn("1 Contact")
            ->shouldReceive('getSafeguardingState')->andReturn("done")
            ->shouldReceive('getSafeguardingStatus')->andReturn("Complete")
            ->shouldReceive('getAccountsState')->andReturn("done")
            ->shouldReceive('getAccountsStatus')->andReturn("1 Account")
            ->shouldReceive('getAssetsState')->andReturn("done")
            ->shouldReceive('getAssetsStatus')->andReturn("1 Asset")
            ->shouldReceive('isReadyToSubmit')->andReturn(true)
            ->getMock();

        $this->reportClient = m::mock('AppBundle\Entity\Client')
            ->shouldIgnoreMissing(true)
            ->shouldReceive("getFullname")->andReturn("Fred Smith")
            ->getMock();
    }   
    
}
