<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service\Stats\Metrics;

use DateTime;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\Attributes\Test;
use Exception;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
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

final class QueryTest extends WebTestCase
{
    private static EntityManager $em;

    public static function setUpBeforeClass(): void
    {
        $kernel = self::bootKernel(['environment' => 'test', 'debug' => false]);

        self::$em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function addUserWithRegistrationDate($date): User
    {
        $id = mt_rand();
        $user = new User();
        $user->setFirstname('Firstname');
        $user->setLastname('Lastname');
        $user->setEmail("metric-test-$id@publicguardian.gov.uk");
        $user->setRoleName('ROLE_PROF_ADMIN');
        $user->setRegistrationDate(new DateTime($date));
        self::$em->persist($user);
        self::$em->flush();

        return $user;
    }

    #[Test]
    public function identifiesUnsupportedDimensions(): void
    {
        $this->expectException(Exception::class);
        $query = new UsersQuery($this::$em);

        $query->execute(new StatsQueryParameters([
            'metric' => 'users',
            'dimension' => ['badDimension'],
        ]));
    }


    #[Test]
    #[DoesNotPerformAssertions]
    public function identifiesSupportedDimensions(): void
    {
        $query = new UsersQuery($this::$em);

        $query->execute(new StatsQueryParameters([
            'metric' => 'users',
            'dimension' => ['roleName', 'ndrEnabled'],
        ]));
    }

    #[Test]
    public function returnsArrayOfDimensions(): void
    {
        $query = new UsersQuery($this::$em);

        $this->addUserWithRegistrationDate('2020-01-01');

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

    #[Test]
    public function returnsValueIfNoDimension(): void
    {
        $query = new UsersQuery($this::$em);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'users',
        ]));

        $this->assertCount(1, $result);
        $this->assertCount(1, $result[0]);
        $this->assertArrayHasKey('amount', $result[0]);
    }

    #[Test]
    public function adheresToDateRange(): void
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

    #[Test]
    public function includesDataFromBothEndDays(): void
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

    #[Test]
    public function ignoresDateRangeIfMetricNotConstrainedByDate(): void
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
