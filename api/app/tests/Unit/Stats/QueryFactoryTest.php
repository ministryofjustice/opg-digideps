<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Stats;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use InvalidArgumentException;
use App\Service\Stats\Query\Query;
use App\Service\Stats\QueryFactory;
use App\Service\Stats\StatsQueryParameters;
use Doctrine\ORM\EntityManager;
use Mockery as m;
use PHPUnit\Framework\TestCase;

final class QueryFactoryTest extends TestCase
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

    /** Initialises real metric queries */
    #[DataProvider('metricNameProvider')]
    #[Test]
    public function initialiseMetricQueries(string $metric): void
    {
        $sq = new StatsQueryParameters([
            'metric' => $metric,
        ]);

        $query = $this->factory->create($sq);
        $this->assertInstanceOf(Query::class, $query);
    }

    /** Throws if the metric query doesn't exist */
    #[Test]
    public function throwIfQueryDoesntExist(): void
    {
        $sq = new StatsQueryParameters([
            'metric' => 'aMetricWeDoNotSupport',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $query = $this->factory->create($sq);
    }
}
