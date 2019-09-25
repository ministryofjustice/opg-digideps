<?php

namespace Tests\AppBundle\Service\Stats;

use AppBundle\Service\Stats\StatsQueryParameters;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class StatsQueryParametersTest extends TestCase
{
    /**
     * @test
     */
    public function populatesProperties()
    {
        $params = new StatsQueryParameters([
            'metric' => 'metric',
            'dimension' => ['dimension1', 'dimension2'],
            'startDate' => '2015-04-04',
            'endDate' => '2015-06-10'
        ]);

        $this->assertEquals('metric', $params->metric);
        $this->assertIsArray($params->dimensions);
        $this->assertContains('dimension1', $params->dimensions);
        $this->assertContains('dimension2', $params->dimensions);
        $this->assertInstanceOf(\DateTime::class, $params->startDate);
        $this->assertEquals('04-04-2015', $params->startDate->format('d-m-Y'));
        $this->assertInstanceOf(\DateTime::class, $params->endDate);
        $this->assertEquals('10-06-2015', $params->endDate->format('d-m-Y'));
    }

    /**
     * @test
     */
    public function requiresMetric()
    {
        $this->expectException(\InvalidArgumentException::class);
        new StatsQueryParameters([]);
    }

    /**
     * @test
     */
    public function requiresDimensionIsNotString()
    {
        $this->expectException(\InvalidArgumentException::class);
        new StatsQueryParameters([
            'metric' => 'metric',
            'dimension' => 'dimension'
        ]);
    }

    /**
     * @test
     */
    public function defaultsMissingDatesToLast30Days()
    {
        $params = new StatsQueryParameters([
            'metric' => 'metric',
            'dimension' => ['dimension']
        ]);

        $expectedStartDate = (new \DateTime())->sub(new \DateInterval('P30D'));
        $expectedEndDate = new \DateTime();

        $this->assertEquals($expectedStartDate->format('d-m-Y'), $params->startDate->format('d-m-Y'));
        $this->assertEquals($expectedEndDate->format('d-m-Y'), $params->endDate->format('d-m-Y'));
    }

    /**
     * @test
     */
    public function defaultsMissingEndDateTo30DaysLater()
    {
        $params = new StatsQueryParameters([
            'metric' => 'metric',
            'dimension' => ['dimension'],
            'startDate' => '2016-08-04'
        ]);

        $this->assertEquals('03-09-2016', $params->endDate->format('d-m-Y'));
    }

    /**
     * @test
     */
    public function defaultsMissingStartDateTo30DaysEarlier()
    {
        $params = new StatsQueryParameters([
            'metric' => 'metric',
            'dimension' => ['dimension'],
            'endDate' => '2018-03-26'
        ]);

        $this->assertEquals('24-02-2018', $params->startDate->format('d-m-Y'));
    }
}
