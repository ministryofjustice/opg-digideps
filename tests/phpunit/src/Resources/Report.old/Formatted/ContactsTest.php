<?php

namespace AppBundle\Resources\views\Report\Formatted;

use AppBundle\Resources\views\Report\AbstractReportTest;
use Symfony\Component\DomCrawler\Crawler;
use Mockery as m;

class ContactsTest extends AbstractReportTest
{
    private $templateName = 'AppBundle:Report:Formatted/_contacts.html.twig';

    public function testShowsContacts()
    {
        $this->setupContacts();

        $html = $this->twig->render($this->templateName, [
            'contacts' => $this->contacts,
        ]);

        $crawler = new Crawler($html);
        $contacts = $crawler->filter('#contacts-section .contacts-list .contact-item');
        $this->assertEquals(2, $contacts->count());
    }

    public function testShowsDetailsForEachDecision()
    {
        $this->setupContacts();

        $html = $this->twig->render($this->templateName, [
            'contacts' => $this->contacts,
        ]);

        $crawler = new Crawler($html);

        $contacts = $crawler->filter('#contacts-section .contacts-list .contact-item');

        $firstContact = $contacts->eq(0)->text();

        $this->assertContains('Any White', $firstContact);
        $this->assertContains('brother', $firstContact);
        $this->assertContains('no explanation', $firstContact);
        $this->assertContains('45 Noth Road', $firstContact);
        $this->assertContains('Islington', $firstContact);
        $this->assertContains('London', $firstContact);
        $this->assertContains('N2 5JF', $firstContact);
        $this->assertContains('United Kingdom', $firstContact);

        $secondContact = $contacts->eq(1)->text();

        $this->assertContains('Fred Smith', $secondContact);
        $this->assertContains('Social Worker', $secondContact);
        $this->assertContains('Advices on benefits and stuff', $secondContact);
        $this->assertContains('Town Hall', $secondContact);
        $this->assertContains('Maidenhead', $secondContact);
        $this->assertContains('Berkshire', $secondContact);
        $this->assertContains('SL1 1YY', $secondContact);
        $this->assertContains('United Kingdom', $secondContact);
    }

    public function testShowWhenNoContacts()
    {
        $this->report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getReasonForNoContacts')->andReturn('we spoke to nobody')
            ->getMock();

        $html = $this->twig->render($this->templateName, [
            'contacts' => [],
            'report' => $this->report,
        ]);

        $crawler = new Crawler($html);

        $noContact = $crawler->filter('#contacts-section #no-contact')->eq(0)->text();

        $this->assertContains('Check this box if you did not consult anyone and use the box below to tell us why.', $noContact);
        $this->assertContains('we spoke to nobody', $noContact);
    }
}
