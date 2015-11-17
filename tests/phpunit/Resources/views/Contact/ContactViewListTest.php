<?php
namespace AppBundle\Resources\views\Contact;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Mockery as m;

class ContactViewListTest extends WebTestCase
{
    /** @var  \Symfony\Bundle\TwigBundle\TwigEngine */
    private $twig;

    /** @var  \Symfony\Bundle\FrameworkBundle\ContainerInterface */
    private $container;


    public function setUp() {

        $client = static::createClient([ 'environment' => 'test','debug' => false]);
        $this->container = $client->getContainer();

        $this->twig = $this->container->get('templating');

        $request = new Request();
        $request->create('/report/1/contacts');
        $this->container->enterScope('request');
        $this->container->set('request', $request, 'request');

    }

    // Continue Button

    /** @test */
    public function showContinueWhenThereAreContacts() {

        $contact = $this->getMockContact();

        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getContacts')->andReturn([$contact])
            ->getMock();


        $html = $this->twig->render('AppBundle:Contact:list.html.twig', [
            'report' => $report,
            'contacts' => [$contact]
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(1, $crawler->filter('#continue-button'));
        $this->assertEquals("/report/1/safeguarding", $crawler->filter('#continue-button')->eq(0)->attr('href'));

    }

    /** @test */
    public function showContinueWhenNoContactsAndReason() {

        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getContacts')->andReturn([])
            ->shouldReceive('getReasonForNoContacts')->andReturn('nothing')
            ->getMock();


        $html = $this->twig->render('AppBundle:Contact:list.html.twig', [
            'report' => $report,
            'contacts' => []
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(1, $crawler->filter('#continue-button'));
        $this->assertEquals("/report/1/safeguarding", $crawler->filter('#continue-button')->eq(0)->attr('href'));

    }

    /** @test */
    public function showContinueWhenNoContactsNoReasonAndDue() {

        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(true)
            ->shouldReceive('getContacts')->andReturn([])
            ->shouldReceive('getReasonForNoContacts')->andReturn("")
            ->getMock();


        $html = $this->twig->render('AppBundle:Contact:list.html.twig', [
            'report' => $report,
            'contacts' => []
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(0, $crawler->filter('#continue-button'));
    }


    // Show List or Add

    /** @test */
    public function showContactsWhenContacts() {

        $client = m::mock('AppBundle\Entity\Client')
            ->shouldIgnoreMissing(true)
            ->getMock();


        $contact = $this->getMockContact();

        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getContacts')->andReturn([$contact])
            ->getMock();


        $html = $this->twig->render('AppBundle:Contact:list.html.twig', [
            'report' => $report,
            'client' => $client,
            'contacts' => [$contact]
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(1, $crawler->filter('#contact-list'));
        $this->assertCount(1, $crawler->filter('#contact-list li'));

    }

    /** @test */
    public function showsAddButton() {

        $contact = $this->getMockContact();

        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(false)
            ->shouldReceive('getContacts')->andReturn([$contact])
            ->getMock();


        $html = $this->twig->render('AppBundle:Contact:list.html.twig', [
            'report' => $report,
            'contacts' => [$contact]
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(1, $crawler->filter('.button-bar-add a'));

    }

    /** @test */
    public function dontShowListWhenNoContacts() {

        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(true)
            ->shouldReceive('getContacts')->andReturn([])
            ->getMock();


        $html = $this->twig->render('AppBundle:Contact:list.html.twig', [
            'report' => $report,
            'contacts' => []
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(0, $crawler->filter('#contact-list'));

    }

    // Show reason for none

    /** @test */
    public function listActionEmbedReasonFormWhenNoReasonAndDue() {
        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(true)
            ->shouldReceive('getContacts')->andReturn([])
            ->shouldReceive('getReasonForNoContacts')->andReturn("")
            ->getMock();


        $html = $this->twig->render('AppBundle:Contact:list.html.twig', [
            'report' => $report,
            'contacts' => []
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(1, $crawler->filter('#no-contact-reason-form-embed'));
        $this->assertCount(0, $crawler->filter('#no-contact-reason-description'));
    }

    /** @test */
    public function showReasonDescriptionWhenReason() {
        // mock data
        $report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('isDue')->andReturn(true)
            ->shouldReceive('getContacts')->andReturn([])
            ->shouldReceive('getReasonForNoContacts')->andReturn("some reason")
            ->getMock();


        $html = $this->twig->render('AppBundle:Contact:list.html.twig', [
            'report' => $report,
            'contacts' => []
        ]);

        $crawler = new Crawler($html);

        $this->assertCount(0, $crawler->filter('#no-contact-reason-form-embed'));
        $this->assertCount(1, $crawler->filter('#no-contact-reason-description'));

    }

    private function getMockContact()
    {
        $contact = m::mock('AppBundle\Entity\Contact')
            ->shouldIgnoreMissing()
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('getContactName')->andReturn("abcd")
            ->shouldReceive('getAddress')->andReturn("abcd")
            ->shouldReceive('getAddress2')->andReturn("abcd")
            ->shouldReceive('getCounty')->andReturn("abcd")
            ->shouldReceive('getPostcode')->andReturn("abcd")
            ->shouldReceive('getCountry')->andReturn("abcd")
            ->shouldReceive('getExplanation')->andReturn("abcd")
            ->getMock();
        
        return $contact;
    }
    
}
