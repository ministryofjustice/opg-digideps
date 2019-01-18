<?php

namespace Tests\AppBundle\Service\RestHandler\Report;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Report;
use AppBundle\Service\RestHandler\Report\DeputyCostsEstimateReportUpdateHandler;
use PHPUnit\Framework\TestCase;

class DeputyCostsEstimateReportUpdateHandlerTest extends TestCase
{
    /** @var DeputyCostsEstimateReportUpdateHandler */
    private $sut;

    /** @var Report | \PHPUnit_Framework_MockObject_MockObject */
    private $report;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->report = $this->getMockBuilder(Report::class)
            ->disableOriginalConstructor()
            ->setMethods(['updateSectionsStatusCache'])
            ->getMock();

        $this->sut = new DeputyCostsEstimateReportUpdateHandler();
    }

    public function testUpdatesHowChargedField()
    {
        $data['prof_deputy_costs_estimate_how_charged'] = 'new-value';

        $this->ensureSectionStatusCacheWillBeUpdated();
        $this->invokeHandler($data);
        $this->assertReportFieldValueIsEqualTo('profDeputyCostsEstimateHowCharged', 'new-value');
    }

    private function ensureSectionStatusCacheWillBeUpdated()
    {
        $this
            ->report
            ->expects($this->once())
            ->method('updateSectionsStatusCache')
            ->with([Report::SECTION_PROF_DEPUTY_COSTS_ESTIMATE]);
    }

    /**
     * @param array $data
     */
    private function invokeHandler(array $data)
    {
        $this->sut->handle($this->report, $data);
    }

    /**
     * @param $field
     * @param $expected
     */
    private function assertReportFieldValueIsEqualTo($field, $expected)
    {
        $getter = sprintf('get%s', ucfirst($field));
        $this->assertEquals($expected, $this->report->$getter());
    }
}
