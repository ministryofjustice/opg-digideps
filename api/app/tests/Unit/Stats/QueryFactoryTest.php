<?php

namespace App\Tests\Unit\Service\Stats;

use App\Service\Stats\Query\Query;
use App\Service\Stats\QueryFactory;
use App\Service\Stats\StatsQueryParameters;
use Doctrine\ORM\EntityManager;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class QueryFactoryTest extends TestCase
{
    public QueryFactory $factory;

    public function setUp(): void
    {
        $em = m::mock(EntityManager::class)->makePartial();
        $this->factory = new QueryFactory($em);
    }

    public static function metricNameProvider(): array
    {
        return [['satisfaction'], ['reportsSubmitted'], ['clients'], ['registeredDeputies']];
    }

    /**
     * @test
     *
     * @dataProvider metricNameProvider
     * Initialises real metric queries
     */
    public function initialiseMetricQueries($metric)
    {
        $sq = new StatsQueryParameters([
            'metric' => $metric,
        ]);

        $query = $this->factory->create($sq);
        $this->assertInstanceOf(Query::class, $query);
    }

    /**
     * @test
     * Throws if the metric query doesn't exist
     */
    public function throwIfQueryDoesntExist()
    {
        $sq = new StatsQueryParameters([
            'metric' => 'aMetricWeDoNotSupport',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $query = $this->factory->create($sq);
    }
}
