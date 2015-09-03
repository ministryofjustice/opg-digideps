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


    public function setUp()
    {

        $this->client = static::createClient([ 'environment' => 'test','debug' => false ]);
        $this->client->getContainer()->enterScope('request');
        $request = new Request();
        $request->create('/');
        $this->client->getContainer()->set('request', $request, 'request');
        
        $this->report = m::mock('AppBundle\Entity\Report');
        $this->report->shouldIgnoreMissing();

    }

    public function tearDown()
    {
        m::close();
    }
    
    public function testContactsSectionContainsOverview()
    {

        $this->setupReport();
        $twig = $this->client->getContainer()->get('templating');

        
        $html = $twig->render('AppBundle:Report:overview.html.twig', [
           'report' => $this->report
        ]);
        
        $crawler = new Crawler($html);

        $this->assertEquals(1, $crawler->filter('#decisions-guidance')->count());
        
    }
    
    private function setupReport() 
    {
        $this->report->shouldReceive('getSubmitted')->andReturn(false);
        $this->report->shouldReceive('getId')->andReturn(1);
        $this->report->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS);
    }   
    

    
}
