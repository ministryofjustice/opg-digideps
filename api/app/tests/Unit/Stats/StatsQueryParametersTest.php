<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Stats;

use PHPUnit\Framework\Attributes\Test;
use DateTime;
use InvalidArgumentException;
use DateInterval;
use App\Service\Stats\StatsQueryParameters;
use PHPUnit\Framework\TestCase;

final class StatsQueryParametersTest extends TestCase
{
    #[Test]
    public function populatesProperties(): void
    {
        $params = new StatsQueryParameters([
            'metric' => 'satisfaction',
            'dimension' => ['dimension1', 'dimension2'],
            'startDate' => '2015-04-04',
            'endDate' => '2015-06-10',
        ]);

        $this->assertEquals('satisfaction', $params->getMetric());
        $this->assertIsArray($params->getDimensions());
        $this->assertContains('dimension1', $params->getDimensions());
        $this->assertContains('dimension2', $params->getDimensions());
        $this->assertInstanceOf(DateTime::class, $params->getStartDate());
        $this->assertEquals('04-04-2015', $params->getStartDate()->format('d-m-Y'));
        $this->assertInstanceOf(DateTime::class, $params->getEndDate());
        $this->assertEquals('10-06-2015', $params->getEndDate()->format('d-m-Y'));
    }

    #[Test]
    public function ignoresDatePropertiesIfMetricNotConstrainedByDates(): void
    {
        $params = new StatsQueryParameters([
            'metric' => 'not-constrained',
            'dimension' => ['dimension1', 'dimension2'],
            'startDate' => '2015-04-04',
            'endDate' => '2015-06-10',
        ]);

        $this->assertNull($params->getStartDate());
        $this->assertNull($params->getEndDate());
    }

    #[Test]
    public function requiresMetric(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new StatsQueryParameters([
            'dimension' => ['dimension1'],
            'startDate' => '2019-05-04',
            'endDate' => '2019-05-31',
        ]);
    }

    #[Test]
    public function requiresDimensionIsNotString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new StatsQueryParameters([
            'metric' => 'metric',
            'dimension' => 'dimension',
            'startDate' => '2019-05-04',
            'endDate' => '2019-05-31',
        ]);
    }

    #[Test]
    public function defaultsMissingDatesToLast30Days(): void
    {
        $params = new StatsQueryParameters([
            'metric' => 'satisfaction',
            'dimension' => ['dimension'],
        ]);

        $expectedStartDate = (new DateTime())->sub(new DateInterval('P30D'));
        $expectedEndDate = new DateTime();

        $this->assertEquals($expectedStartDate->format('d-m-Y'), $params->getStartDate()->format('d-m-Y'));
        $this->assertEquals($expectedEndDate->format('d-m-Y'), $params->getEndDate()->format('d-m-Y'));
    }

    #[Test]
    public function defaultsMissingEndDateTo30DaysLater(): void
    {
        $params = new StatsQueryParameters([
            'metric' => 'satisfaction',
            'dimension' => ['dimension'],
            'startDate' => '2016-08-04',
        ]);

        $this->assertEquals('03-09-2016', $params->getEndDate()->format('d-m-Y'));
    }

    #[Test]
    public function defaultsMissingStartDateTo30DaysEarlier(): void
    {
        $params = new StatsQueryParameters([
            'metric' => 'satisfaction',
            'dimension' => ['dimension'],
            'endDate' => '2018-03-26',
        ]);

        $this->assertEquals('24-02-2018', $params->getStartDate()->format('d-m-Y'));
    }
}
