<?php
namespace phpunit\AppBundle\Resources\views\Report;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Report as Report;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Mockery as m;

class ClientInformationTest extends WebTestCase
{

    /**
     * @var Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    private $report;
    private $reportClient;
    private $deputy;
    private $decisions;

    private $twig;

    private $templateName = 'AppBundle:Report:Formatted/_decisions.html.twig';

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

    public function testHeadings() {
        $this->setupDecisions();
    }

    public function testShowsDecisions()
    {
        $this->setupDecisions();
        $this->setupReport();

        $html = $this->twig->render($this->templateName, [
            'decisions' => $this->decisions,
            'report' => $this->report
        ]);

        $crawler = new Crawler($html);

        $title = $crawler->filter('#decisions-section h2')->eq(0)->text();



        $caseNumber = $crawler->filter('#case-number')->eq(0)->text();
        $startDate =  $crawler->filter('#report-start-date')->eq(0)->text();
        $endDate = $crawler->filter('#report-end-date')->eq(0)->text();


        $this->assertContains('12341234', $caseNumber);
        $this->assertContains('01 / 01 / 2014', $startDate);
        $this->assertContains('01 / 01 / 2015', $endDate);


    }

    public function testShowWhenNoDecisions()
    {
        $this->setupDecisions();
        $this->setupReport();

        $html = $this->twig->render($this->templateName, [
            'decisions' => $this->decisions,
            'report' => $this->report
        ]);


    }



    private function setupDecisions()
    {
        $decision1 = m::mock('AppBundle\Entity\Decision')
            ->shouldReceive('getDescription')->andReturn('3 beds')
            ->shouldReceive('getClientInvolved')->andReturn(true)
            ->shouldReceive('getClientInvolvedDeails')->andReturn("the client was able to decide at 85%")
            ->getMock();

        $decision2 = m::mock('AppBundle\Entity\Decision')
            ->shouldReceive('getDescription')->andReturn('2 televisions')
            ->shouldReceive('getClientInvolved')->andReturn(false)
            ->shouldReceive('getClientInvolvedDeails')->andReturn("the client said he doesnt want a tv anymore")
            ->getMock();

        $this->decisions = [$decision1, $decision2];

    }

    private function setupReport()
    {
        $startDate = \DateTime::createFromFormat('j-M-Y', '1-Jan-2014');
        $endDate = \DateTime::createFromFormat('j-M-Y', '1-Jan-2015');

        $this->report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getSubmitted')->andReturn(false)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->shouldReceive('getStartDate')->andReturn($startDate)
            ->shouldReceive('getEndDate')->andReturn($endDate)
            ->shouldReceive('getDecisions')->andReturn($this->decisions)
            ->getMock();
    }

    private function setupReportClient()
    {
        $this->reportClient = m::mock('AppBundle\Entity\Client')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->andReturn('12341234')
            ->shouldReceive('getFirstname')->andReturn('Leroy')
            ->shouldReceive('getLastname')->andReturn('Cross-Tolley')
            ->shouldReceive('getAddress')->andReturn('Blackthorn Cottage')
            ->shouldReceive('getAddress2')->andReturn('Chawridge Lane')
            ->shouldReceive('getCounty')->andReturn('Berkshire')
            ->shouldReceive('getPostcode')->andReturn('SL4 4QR')
            ->shouldReceive('getPhone')->andReturn('07814 013561')
            ->getMock();
    }

    private function setupDeputy()
    {
        $this->deputy = m::mock('AppBundle\Entity\User')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getFirstname')->andReturn('Zac')
            ->shouldReceive('getLastname')->andReturn('Tolley')
            ->shouldReceive('getAddress1')->andReturn('Blackthorn Cottage')
            ->shouldReceive('getAddress2')->andReturn('Chawridge Lane')
            ->shouldReceive('getAddress3')->andReturn('Berkshire')
            ->shouldReceive('getAddressPostcode')->andReturn('SL4 4QR')
            ->shouldReceive('getPhoneMain')->andReturn('07814 013561')
            ->shouldReceive('getEmail')->andReturn('zac@thetolleys.com')
            ->getMock();
    }


}
