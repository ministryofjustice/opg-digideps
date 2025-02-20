<?php

namespace App\Tests\Integration\Service\Stats\Metrics;

use App\Entity\User;
use App\Service\Stats\Query\Query;
use App\Service\Stats\StatsQueryParameters;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UsersQuery extends Query
{
    protected function getAggregation(): string
    {
        return 'COUNT(1)';
    }

    protected function getSupportedDimensions(): array
    {
        return ['roleName', 'ndrEnabled'];
    }

    public function getSubquery(): string
    {
        return '
            SELECT
                id,
                registration_date date,
                role_name roleName,
                odr_enabled ndrEnabled
            FROM dd_user
        ';
    }
}

class QueryTest extends WebTestCase
{
    /**
     * @var EntityManager
     */
    protected static $em;

    public static function setUpBeforeClass(): void
    {
        $kernel = self::bootKernel(['environment' => 'test', 'debug' => false]);

        self::$em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function addUserWithRegistrationDate($date)
    {
        $id = mt_rand();
        $user = new User();
        $user->setFirstname('Firstname');
        $user->setLastname('Lastname');
        $user->setEmail("metric-test-$id@publicguardian.gov.uk");
        $user->setRoleName('ROLE_PROF_ADMIN');
        $user->setRegistrationDate(new \DateTime($date));
        self::$em->persist($user);
        self::$em->flush();

        return $user;
    }

    /**
     * @test
     */
    public function identifiesUnsupportedDimensions()
    {
        $this->expectException(\Exception::class);
        $query = new UsersQuery($this::$em);

        $query->execute(new StatsQueryParameters([
            'metric' => 'users',
            'dimension' => ['badDimension'],
        ]));
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function identifiesSupportedDimensions()
    {
        $query = new UsersQuery($this::$em);

        $query->execute(new StatsQueryParameters([
            'metric' => 'users',
            'dimension' => ['roleName', 'ndrEnabled'],
        ]));
    }

    /**
     * @test
     */
    public function returnsArrayOfDimensions()
    {
        $query = new UsersQuery($this::$em);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'users',
            'dimension' => ['roleName', 'ndrEnabled'],
        ]));

        $this->assertContainsOnly('array', $result);

        $this->assertCount(3, $result[0]);
        $this->assertArrayHasKey('amount', $result[0]);
        $this->assertArrayHasKey('roleName', $result[0]);
        $this->assertArrayHasKey('ndrEnabled', $result[0]);
    }

    /**
     * @test
     */
    public function returnsValueIfNoDimension()
    {
        $query = new UsersQuery($this::$em);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'users',
        ]));

        $this->assertCount(1, $result);
        $this->assertCount(1, $result[0]);
        $this->assertArrayHasKey('amount', $result[0]);
    }

    /**
     * @test
     */
    public function adheresToDateRange()
    {
        $query = new UsersQuery($this::$em);

        $this->addUserWithRegistrationDate('2016-05-04');
        $this->addUserWithRegistrationDate('2016-11-27');

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'satisfaction',
            'startDate' => '2016-05-01',
            'endDate' => '2016-05-31',
        ]));

        $this->assertEquals(1, $result[0]['amount']);
    }

    /**
     * @test
     */
    public function includesDataFromBothEndDays()
    {
        $query = new UsersQuery($this::$em);

        $result1 = $query->execute(new StatsQueryParameters([
            'metric' => 'satisfaction',
            'startDate' => '2016-05-01',
            'endDate' => '2016-05-04',
        ]));

        $this->assertEquals(1, $result1[0]['amount']);

        $result2 = $query->execute(new StatsQueryParameters([
            'metric' => 'satisfaction',
            'startDate' => '2016-05-04',
            'endDate' => '2016-05-31',
        ]));

        $this->assertEquals(1, $result2[0]['amount']);
    }

    /**
     * @test
     */
    public function ignoresDateRangeIfMetricNotConstrainedByDate()
    {
        $query = new UsersQuery($this::$em);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'not-constrained',
            'startDate' => '2016-05-01',
            'endDate' => '2016-05-31',
        ]));

        $this->assertGreaterThanOrEqual(2, $result[0]['amount']);
    }
}
