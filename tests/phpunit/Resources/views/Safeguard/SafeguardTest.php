<?php
namespace AppBundle\Resources\views\Safeguard;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Mockery as m;

class SafeguardTest extends WebTestCase
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


        $html = $this->twig->render('AppBundle:Safeguard:edit.html.twig', [
            'report' => $report,
            'action' => 'list'
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(1, $crawler->filter('nav.pagination .previous'));
        $this->assertEquals("/report/1/contacts", $crawler->filter('nav.pagination .previous a')->eq(0)->attr('href'));
        $this->assertEquals("Contacts", $crawler->filter('nav.pagination .previous .pagination-part-title')->eq(0)->text());


        $this->assertCount(1, $crawler->filter('nav.pagination .next'));
        $this->assertEquals("/report/1/safeguarding", $crawler->filter('nav.pagination .next a')->eq(0)->attr('href'));
        $this->assertEquals("Safeguarding", $crawler->filter('nav.pagination .next .pagination-part-title')->eq(0)->text());

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


        $html = $this->twig->render('AppBundle:Safeguard:edite.html.twig', [
            'report' => $report,
            'action' => 'list'
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(0, $crawler->filter('nav.pagination .next'));
        $this->assertCount(0, $crawler->filter('nav.pagination .previous'));


    }
}
