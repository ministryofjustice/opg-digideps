<?php

namespace AppBundle\Resources\views\Report\Formatted;

use AppBundle\Resources\views\Report\AbstractReportTest;
use Symfony\Component\DomCrawler\Crawler;
use Mockery as m;

class ClientInformationTest extends AbstractReportTest
{
    private $templateName = 'AppBundle:Report:Formatted/_client_information.html.twig';

    public function testShowsCaseInformation()
    {
        $this->setupReport();
        $this->setupDeputy();
        $this->setupReportClient();

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report,
            'client' => $this->reportClient,
            'deputy' => $this->deputy,
        ]);

        $crawler = new Crawler($html);

        $caseNumber = $crawler->filter('#case-number')->eq(0)->text();
        $startDate = $crawler->filter('#report-start-date')->eq(0)->text();
        $endDate = $crawler->filter('#report-end-date')->eq(0)->text();

        $this->assertContains('12341234', $caseNumber);
        $this->assertContains('01 / 01 / 2014', $startDate);
        $this->assertContains('01 / 01 / 2015', $endDate);
    }

    public function testShowDeputyInformation()
    {
        $this->setupReport();
        $this->setupDeputy();
        $this->setupReportClient();

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report,
            'client' => $this->reportClient,
            'deputy' => $this->deputy,
        ]);

        $crawler = new Crawler($html);

        $deputy = $crawler->filter('#deputy-details-subsection')->eq(0)->text();

        $this->assertContains('Zac', $deputy);
        $this->assertContains('Tolley', $deputy);
        $this->assertContains('Blackthorn Cottage', $deputy);
        $this->assertContains('Chawridge Lane', $deputy);
        $this->assertContains('Berkshire', $deputy);
        $this->assertContains('SL4 4QR', $deputy);
        $this->assertContains('07814 013561', $deputy);

        $email = $crawler->filter('#deputy-email')->eq(0)->text();
        $this->assertContains('zac@thetolleys.com', $email);
    }

    public function testShowDeputyInformationWithPartialAddress()
    {
        $this->setupReport();
        $this->setupReportClient();

        $this->deputy = m::mock('AppBundle\Entity\User')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getFirstname')->andReturn('Zac')
            ->shouldReceive('getLastname')->andReturn('Tolley')
            ->shouldReceive('getAddress1')->andReturn('Blackthorn Cottage')
            ->shouldReceive('getAddress3')->andReturn('Berkshire')
            ->shouldReceive('getAddressPostcode')->andReturn('SL4 4QR')
            ->shouldReceive('getEmail')->andReturn('zac@thetolleys.com')
            ->getMock();

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report,
            'client' => $this->reportClient,
            'deputy' => $this->deputy,
        ]);

        $crawler = new Crawler($html);

        $deputy = $crawler->filter('#deputy-details-subsection')->eq(0)->text();

        $this->assertContains('Zac', $deputy);
        $this->assertContains('Tolley', $deputy);
        $this->assertContains('Blackthorn Cottage', $deputy);
        $this->assertContains('Berkshire', $deputy);
        $this->assertContains('SL4 4QR', $deputy);

        $email = $crawler->filter('#deputy-email')->eq(0)->text();
        $this->assertContains('zac@thetolleys.com', $email);
    }

    public function testShowClientInformation()
    {
        $this->setupReport();
        $this->setupDeputy();
        $this->setupReportClient();

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report,
            'client' => $this->reportClient,
            'deputy' => $this->deputy,
        ]);

        $crawler = new Crawler($html);

        $client = $crawler->filter('#client-details-subsection')->eq(0)->text();

        $this->assertContains('Leroy', $client);
        $this->assertContains('Cross-Tolley', $client);
        $this->assertContains('Blackthorn Cottage', $client);
        $this->assertContains('Chawridge Lane', $client);
        $this->assertContains('Berkshire', $client);
        $this->assertContains('SL4 4QR', $client);
        $this->assertContains('07814 013561', $client);
    }

    public function testShowClientInformationWithPartialAddress()
    {
        $this->setupReport();
        $this->setupDeputy();

        $this->reportClient = m::mock('AppBundle\Entity\Client')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getFirstname')->andReturn('Leroy')
            ->shouldReceive('getLastname')->andReturn('Cross-Tolley')
            ->shouldReceive('getAddress')->andReturn('Blackthorn Cottage')
            ->shouldReceive('getCounty')->andReturn('Berkshire')
            ->shouldReceive('getPostcode')->andReturn('SL4 4QR')
            ->getMock();

        $html = $this->twig->render($this->templateName, [
            'report' => $this->report,
            'client' => $this->reportClient,
            'deputy' => $this->deputy,
        ]);

        $crawler = new Crawler($html);

        $client = $crawler->filter('#client-details-subsection')->eq(0)->text();

        $this->assertContains('Leroy', $client);
        $this->assertContains('Cross-Tolley', $client);
        $this->assertContains('Blackthorn Cottage', $client);
        $this->assertContains('Berkshire', $client);
        $this->assertContains('SL4 4QR', $client);
    }
}
