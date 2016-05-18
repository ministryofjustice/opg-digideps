<?php

namespace AppBundle\Resources\views\Report\Formatted;

use AppBundle\Resources\views\Report\AbstractReportTest;
use Symfony\Component\DomCrawler\Crawler;
use Mockery as m;

class DecisionsTest extends AbstractReportTest
{
    private $templateName = 'AppBundle:Report:Formatted/_decisions.html.twig';

    public function testHeadings()
    {
        $this->setupDecisions();

        $this->setupDecisions();
        $this->setupReport();

        $html = $this->twig->render($this->templateName, [
            'decisions' => $this->decisions,
        ]);

        $crawler = new Crawler($html);

        $title = $crawler->filter('#decisions-section h2')->eq(0)->text();

        $this->assertContains('Decisions made over the reporting period', $title);
    }

    public function testShowsDecisions()
    {
        $this->setupDecisions();
        $this->setupReport();

        $html = $this->twig->render($this->templateName, [
            'decisions' => $this->decisions,
        ]);

        $crawler = new Crawler($html);
        $decisions = $crawler->filter('#decisions-section .decisions-list .decision-item');
        $this->assertEquals(2, $decisions->count());
    }

    public function testShowsDetailsForEachDecision()
    {
        $this->setupDecisions();
        $this->setupReport();

        $html = $this->twig->render($this->templateName, [
            'decisions' => $this->decisions,
        ]);

        $crawler = new Crawler($html);

        $decisions = $crawler->filter('#decisions-section .decisions-list .decision-item');

        $firstDecision = $decisions->eq(0)->text();

        $this->assertContains('3 beds', $firstDecision);
        $this->assertContains('the client was able to decide at 85%', $firstDecision);

        $secondDecision = $decisions->eq(1)->text();

        $this->assertContains('2 televisions', $secondDecision);
        $this->assertContains('the client said he doesnt want a tv anymore', $secondDecision);
    }

    public function testShowWhenNoDecisions()
    {
        $this->report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getReasonForNoDecisions')->andReturn('no one could decide')
            ->getMock();

        $html = $this->twig->render($this->templateName, [
            'decisions' => [],
            'report' => $this->report,
        ]);

        $crawler = new Crawler($html);

        $decisions = $crawler->filter('#decisions-section .decisions-list .decision-item');

        $firstDecision = $decisions->eq(0)->text();

        $this->assertContains('No decisions made', $firstDecision);
        $this->assertContains('no one could decide', $firstDecision);
    }
}
