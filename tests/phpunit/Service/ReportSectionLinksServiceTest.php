<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\ReportInterface;
use MockeryStub as m;
use Symfony\Component\Routing\RouterInterface;

class ReportSectionLinksServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReportSectionsLinkService
     */
    protected $sut;

    /**
     * Set up the mockservies
     */
    public function setUp()
    {
        $this->router = m::mock(RouterInterface::class);
        $this->router->shouldReceive('generate')->withAnyArgs()->andReturnUsing(function ($a, $b) {
            return $a . '-link-' . print_r($b, true);
        });
        $this->report = m::mock(ReportInterface::class);

        $this->report->shouldReceive('getId')->andReturn('1');
        $this->report->shouldReceive('hasSection')->andReturn(true);
        //$this->report->shouldReceive('getType')->andReturn('irrelevant');

        $this->sut = new ReportSectionsLinkService($this->router);
    }

    public function testgetSectionParamsLay()
    {
//        $this->report->shouldReceive('isLayReport')->andReturn(true);

        $actual = $this->sut->getSectionParams($this->report, 'contacts', -1);
        $this->assertEquals('decisions', $actual['section']);
        $this->assertContains('decisions-link-', $actual['link']);

        $actual = $this->sut->getSectionParams($this->report, 'contacts', 1);
        $this->assertEquals('visitsCare', $actual['section']);

        $actual = $this->sut->getSectionParams($this->report, 'documents', -1);
        $this->assertEquals('otherInfo', $actual['section']);

        $actual = $this->sut->getSectionParams($this->report, 'documents', +1);
        $this->assertEquals([], $actual);
    }

    public function testgetSectionParamsOrg()
    {
//        $this->report->shouldReceive('isLayReport')->andReturn(false);

        $actual = $this->sut->getSectionParams($this->report, 'profCurrentFees', +1);
        $this->assertEquals('gifts', $actual['section']);
    }

    public function tearDown()
    {
        m::close();
    }
}
