<?php

namespace AppBundle\Resources\views\Report\Formatted;

use Symfony\Component\DomCrawler\Crawler;
use AppBundle\Resources\views\Report\AbstractReportTest;
use Mockery as m;

class AssetsTest extends AbstractReportTest
{
    private $groupAssets;

    private $templateName = 'AppBundle:Report:Formatted/_assets.html.twig';

    public function setUp()
    {
        parent::setUp();
        $this->setupReport();
        $this->groupAssets = [];
    }

    private function getDom()
    {
        $html = $this->twig->render($this->templateName, [
            'report' => $this->report,
            'groupAssets' => $this->groupAssets,
        ]);

        $crawler = new Crawler($html);

        return $crawler->filter('#assets-section');
    }

    public function testEmpty()
    {
        $this->report->shouldReceive('getAssets')->atLeast(1)->andReturn([]);

        $this->assertContains('My client has no assets', $this->getDom()->filter('fieldset')->text());
    }

    public function testDisplayedAndTotals()
    {
        // mock data
        $this->groupAssets['Property'] = [
            m::mock('AppBundle\Entity\Asset')
                ->shouldReceive('getDescription')->atLeast(1)->andReturn('property1')
                ->shouldReceive('getValue')->atLeast(1)->andReturn(250000)
                ->shouldReceive('getValuationDate')->atLeast(1)->andReturn(new \DateTime('2015-12-31'))
                ->getMock(),
            m::mock('AppBundle\Entity\Asset')
                ->shouldReceive('getDescription')->atLeast(1)->andReturn('property2')
                ->shouldReceive('getValue')->atLeast(1)->andReturn(120000)
                ->shouldReceive('getValuationDate')->atLeast(1)->andReturn(null)
                ->getMock(),
        ];

        $this->groupAssets['Vehicles'] = [
            m::mock('AppBundle\Entity\Asset')
                ->shouldReceive('getDescription')->atLeast(1)->andReturn('car1')
                ->shouldReceive('getValue')->atLeast(1)->andReturn(12001.02)
                ->shouldReceive('getValuationDate')->atLeast(1)->andReturn(null)
                ->getMock(),
        ];

        $this->report->shouldReceive('getAssets')->atLeast(1)->andReturn([1, 2]);

        $ul = $this->getDom()->filter('ul.asset-list');

        // assert group count
        $this->assertCount(2, $ul->filter('li.asset-group'));

        // assert content
        $property1 = $ul->filter('li.asset-group')->eq(0)->filter('ul.asset-group-items li.asset-item')->eq(0);
        $this->assertContains('property1', $property1->filter('.asset-description .value')->text());
        $this->assertContains('31 / 12 / 2015', $property1->filter('.asset-valuationDate .value')->text());
        $this->assertContains('£250,000.00', $property1->filter('.asset-value .value')->text());

        $property2 = $ul->filter('li.asset-group')->eq(0)->filter('ul.asset-group-items li.asset-item')->eq(1);
        $this->assertContains('property2', $property2->filter('.asset-description .value')->text());
        $this->assertEquals('', $property2->filter('.asset-valuationDate .value')->text());
        $this->assertContains('£120,000.00', $property2->filter('.asset-value .value')->text());

        $car1 = $ul->filter('li.asset-group')->eq(1)->filter('ul.asset-group-items li.asset-item')->eq(0);
        $this->assertContains('car1', $car1->filter('.asset-description .value')->text());
        $this->assertEquals('', $car1->filter('.asset-valuationDate .value')->text());
        $this->assertContains('£12,001.02', $car1->filter('.asset-value .value')->text());

        // assert totals
        $this->assertContains('£382,001.02', $ul->filter('fieldset.assets-total-value .assets-total-value .value')->text());
    }
}
