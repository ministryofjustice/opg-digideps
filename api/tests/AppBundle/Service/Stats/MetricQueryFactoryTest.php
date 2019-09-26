<?php

namespace Tests\AppBundle\Service\Stats;

use AppBundle\Service\Stats\Metrics\MetricQuery;
use AppBundle\Service\Stats\MetricQueryFactory;
use AppBundle\Service\Stats\StatsQueryParameters;
use Doctrine\ORM\EntityManager;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class MetricQueryFactoryTest extends TestCase
{
    /**
     * @var MetricQueryFactory
     */
    public $factory;

    public function setUp(): void
    {
        $em = m::mock(EntityManager::class)->makePartial();
        $this->factory = new MetricQueryFactory($em);
    }

    /**
     * Provider of metric names
     */
    public function metricNameProvider()
    {
        return [['satisfaction'], ['reportsSubmitted'], ['clients'], ['registeredDeputies']];
    }

    /**
     * @test
     * @dataProvider metricNameProvider
     * Initialises real metric queries
     */
    public function initialiseMetricQueries($metric)
    {
        $sq = new StatsQueryParameters([
            'metric' => $metric
        ]);

        $query = $this->factory->create($sq);
        $this->assertInstanceOf(MetricQuery::class, $query);
    }

    /**
     * @test
     * Throws if the metric query doesn't exist
     */
    public function throwIfQueryDoesntExist()
    {
        $sq = new StatsQueryParameters([
            'metric' => 'aMetricWeDoNotSupport'
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $query = $this->factory->create($sq);
    }
}
