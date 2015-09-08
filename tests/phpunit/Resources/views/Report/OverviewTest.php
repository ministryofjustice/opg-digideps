<?php
namespace phpunit\AppBundle\Resources\views\Report;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Report as Report;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Mockery as m;

class OverviewTest extends WebTestCase
{

    /**
     * @var Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;
    
    private $report;

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
    
    public function testDecisionSectionContainsOverview()
    {
        $this->setupReport();
        
        $html = $this->twig->render('AppBundle:Report:overview.html.twig', [
           'report' => $this->report
        ]);
        
        $crawler = new Crawler($html);
        $decisionsGuidance = $crawler->filter('#decisions-guidance');
        
        $this->assertEquals(1, $decisionsGuidance->count());
        
        $guidanceElementText = $decisionsGuidance->eq(0)->text();
        
        $this->assertContains("Let us know the significant decisions you make over the reporting period. A significant decision is any important decision you make for the client during the reporting period. A significant decision might be:", $guidanceElementText);
        $this->assertContains("buying or selling or renting property", $guidanceElementText);
        $this->assertContains("buying or selling investments or shares", $guidanceElementText);
        $this->assertContains("loans", $guidanceElementText);
        $this->assertContains("making gifts", $guidanceElementText);
        $this->assertContains("moving the client to another nursing or care home", $guidanceElementText);
    }

    public function testContactsSectionContainsOverview()
    {
        $this->setupReport();
        
        $html = $this->twig->render('AppBundle:Report:overview.html.twig', [
            'report' => $this->report
        ]);

        $crawler = new Crawler($html);
        $guidanceElements = $crawler->filter('#contacts-guidance');

        $this->assertEquals(1, $guidanceElements->count());

        $guidanceElementText = $guidanceElements->eq(0)->text();

        $this->assertContains("Let us know the contact details of the people you consult over the reporting period, such as:", $guidanceElementText);
        $this->assertContains("care home staff or social services", $guidanceElementText);
        $this->assertContains("family members", $guidanceElementText);
        $this->assertContains("close friends", $guidanceElementText);
        $this->assertContains("GP and other health staff", $guidanceElementText);
        $this->assertContains("We need to know the the client's general situation. You don't need to list every person you contact, but we need an idea of the people you consult when deciding for the client - especially for important decisions.", $guidanceElementText);
    }

    public function testSafeguardingSectionContainsOverview()
    {
        $this->setupReport();

        $html = $this->twig->render('AppBundle:Report:overview.html.twig', [
            'report' => $this->report
        ]);

        $crawler = new Crawler($html);
        $guidanceElements = $crawler->filter('#safeguarding-guidance');

        $this->assertEquals(1, $guidanceElements->count());

        $guidanceElementText = $guidanceElements->eq(0)->text();

        $this->assertContains("Let us know how the client is cared for and what contact they have with you and other people.", $guidanceElementText);
        $this->assertContains("We need to know how you check their needs are met. The OPG has a duty protect those that don’t have mental capacity to make decisions for themselves.", $guidanceElementText);
    }

    public function testAccountSectionContainsOverview()
    {
        $this->setupReport();

        $html = $this->twig->render('AppBundle:Report:overview.html.twig', [
            'report' => $this->report
        ]);

        $crawler = new Crawler($html);
        $guidanceElements = $crawler->filter('#accounts-guidance');

        $this->assertEquals(1, $guidanceElements->count());

        $guidanceElementText = $guidanceElements->eq(0)->text();

        $this->assertContains("Add details of your client’s accounts. We need to know the totals for the different types of payments you make and money you receive for the client. It's easiest to fill this in toward the end of your reporting period, when you know the final total amounts.", $guidanceElementText);
        $this->assertContains("You can only sign off the accounts section at the end of the reporting period when you know the final totals.", $guidanceElementText);
    }

    public function testAssetsSectionContainsOverview()
    {
        $this->setupReport();

        $html = $this->twig->render('AppBundle:Report:overview.html.twig', [
            'report' => $this->report
        ]);

        $crawler = new Crawler($html);
        $guidanceElements = $crawler->filter('#assets-guidance');

        $this->assertEquals(1, $guidanceElements->count());

        $guidanceElementText = $guidanceElements->eq(0)->text();

        $this->assertContains("Add details of the client's assets and saving, such as:", $guidanceElementText);
        $this->assertContains("property", $guidanceElementText);
        $this->assertContains("savings and investments", $guidanceElementText);
        $this->assertContains("stocks and shares, premium bonds", $guidanceElementText);
        $this->assertContains("artwork, antiques or jewellery", $guidanceElementText);

    }
    
    private function setupReport() 
    {
        $this->report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getSubmitted')->andReturn(false)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->getMock();
    }   
    

    
}
