<?php
namespace phpunit\AppBundle\Resources\views\Decision;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Account;
use AppBundle\Entity\Report;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Fixtures;
use Mockery as m;

class ContactContinueTest extends WebTestCase
{
    public function setUp() {
        $client = static::createClient([ 'environment' => 'test',
            'debug' => false]);
        $this->twig = $client->getContainer()->get('templating');
    }


    /** @test */
    public function showContinueInListWithEntries() {

        $contact = m::mock('AppBundle\Entity\Contact');

        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getContacts')->andReturn([$contact])
            ->getMock();


        $html = $this->twig->render('AppBundle:Contact:_continue.html.twig', [
            'report' => $report,
            'action' => 'list'
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(1, $crawler->filter('#continue-button'));
        $this->assertEquals("/report/1/safeguarding", $crawler->filter('#continue-button')->eq(0)->attr('href'));

    }

    /** @test */
    public function showContinueInListWithReasonForNoDecisions() {

        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getContacts')->andReturn([])
            ->shouldReceive('getReasonForNoContacts')->andReturn('nothing')
            ->getMock();


        $html = $this->twig->render('AppBundle:Contact:_continue.html.twig', [
            'report' => $report,
            'action' => 'list'
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(1, $crawler->filter('#continue-button'));
        $this->assertEquals("/report/1/safeguarding", $crawler->filter('#continue-button')->eq(0)->attr('href'));
    }

    /** @test */
    public function dontShowContinueInListWhenAdding() {
        $contact = m::mock('AppBundle\Entity\Contact');

        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getContacts')->andReturn([$contact])
            ->getMock();


        $html = $this->twig->render('AppBundle:Contact:_continue.html.twig', [
            'report' => $report,
            'action' => 'add'
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(0, $crawler->filter('#continue-button'));
    }

    /** @test */
    public function dontShowContinueInListWhenEditing() {
        $contact = m::mock('AppBundle\Entity\Contact');

        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getContacts')->andReturn([$contact])
            ->getMock();


        $html = $this->twig->render('AppBundle:Contact:_continue.html.twig', [
            'report' => $report,
            'action' => 'edit-reason'
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(0, $crawler->filter('#continue-button'));
    }

    /** @test */
    public function dontShowWhenDeleteDecision() {
        $contact = m::mock('AppBundle\Entity\Contact');

        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getContacts')->andReturn([$contact])
            ->getMock();


        $html = $this->twig->render('AppBundle:Contact:_continue.html.twig', [
            'report' => $report,
            'action' => 'delete-confirm'
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(0, $crawler->filter('#continue-button'));
    }

    /** @test */
    public function dontShowWhenDeleteNoReason() {
        $contact = m::mock('AppBundle\Entity\Contact');

        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getDecisions')->andReturn([$contact])
            ->getMock();


        $html = $this->twig->render('AppBundle:Contact:_continue.html.twig', [
            'report' => $report,
            'action' => 'delete-reason-confirm'
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(0, $crawler->filter('#continue-button'));
    }

    /** @test */
    public function dontShowContinueWhenFirstAccessingPageAndNotDue() {

        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getContacts')->andReturn([])
            ->shouldReceive('getReasonForNoContacts')->andReturn("")
            ->getMock();


        $html = $this->twig->render('AppBundle:Contact:_continue.html.twig', [
            'report' => $report,
            'action' => 'list'
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(0, $crawler->filter('#continue-button'));
    }

    /** @test */
    public function dontShowContinueWhenFirstAccessingPageAndDue() {

        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(true)
            ->shouldReceive('getContacts')->andReturn([])
            ->shouldReceive('getReasonForNoContacts')->andReturn("")
            ->getMock();


        $html = $this->twig->render('AppBundle:Contact:_continue.html.twig', [
            'report' => $report,
            'action' => 'list'
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(0, $crawler->filter('#continue-button'));
    }

}
