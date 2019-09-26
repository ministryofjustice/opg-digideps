<?php

namespace Tests\AppBundle\Service\Stats;

use AppBundle\Entity\User;
use AppBundle\Service\Stats\Metrics\MetricQuery;
use AppBundle\Service\Stats\StatsQueryParameters;
use Mockery as m;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MetricUsersQuery extends MetricQuery
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
        return "
            SELECT
                id,
                registration_date date,
                role_name roleName,
                odr_enabled ndrEnabled
            FROM dd_user
        ";
    }
};

class MetricQueryTest extends WebTestCase
{
    /**
     * @var EntityManager
     */
    protected static $em;

    public static function setUpBeforeClass(): void
    {
        $frameworkBundleClient = static::createClient(['environment' => 'test', 'debug' => false]);
        self::$em = $frameworkBundleClient->getContainer()->get('em');
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
        $query = new MetricUsersQuery($this::$em);

        $query->execute(new StatsQueryParameters([
            'metric' => 'users',
            'dimension' => ['badDimension']
        ]));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function identifiesSupportedDimensions()
    {
        $query = new MetricUsersQuery($this::$em);

        $query->execute(new StatsQueryParameters([
            'metric' => 'users',
            'dimension' => ['roleName', 'ndrEnabled']
        ]));
    }

    /**
     * @test
     */
    public function returnsArrayOfDimensions() {
        $query = new MetricUsersQuery($this::$em);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'users',
            'dimension' => ['roleName', 'ndrEnabled']
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
    public function returnsValueIfNoDimension() {
        $query = new MetricUsersQuery($this::$em);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'users'
        ]));

        $this->assertCount(1, $result);
        $this->assertCount(1, $result[0]);
        $this->assertArrayHasKey('amount', $result[0]);
    }

    /**
     * @test
     */
    public function adheresToDateRange() {
        $query = new MetricUsersQuery($this::$em);

        $this->addUserWithRegistrationDate('2016-05-04');

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'users',
            'startDate' => '2016-05-01',
            'endDate' => '2016-05-31',
        ]));

        $this->assertEquals(1, $result[0]['amount']);
    }

    /**
     * @test
     */
    public function includesDataFromBothEndDays() {
        $query = new MetricUsersQuery($this::$em);

        $result1 = $query->execute(new StatsQueryParameters([
            'metric' => 'users',
            'startDate' => '2016-05-01',
            'endDate' => '2016-05-04',
        ]));

        $this->assertEquals(1, $result1[0]['amount']);

        $result2 = $query->execute(new StatsQueryParameters([
            'metric' => 'users',
            'startDate' => '2016-05-04',
            'endDate' => '2016-05-31',
        ]));

        $this->assertEquals(1, $result2[0]['amount']);
    }
}
