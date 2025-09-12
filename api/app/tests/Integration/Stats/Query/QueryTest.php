<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service\Stats\Metrics;

use App\Tests\Integration\ApiTestCase;
use App\Tests\Integration\TestHelpers\UsersQuery;
use DateTime;
use App\Entity\User;
use App\Service\Stats\StatsQueryParameters;

class QueryTest extends ApiTestCase
{
    private function addUserWithRegistrationDate(string $date): User
    {
        $id = mt_rand();
        $user = new User();
        $user->setFirstname('Firstname');
        $user->setLastname('Lastname');
        $user->setEmail("metric-test-$id@publicguardian.gov.uk");
        $user->setRoleName('ROLE_PROF_ADMIN');
        $user->setRegistrationDate(new DateTime($date));

        self::$entityManager->persist($user);
        self::$entityManager->flush();

        return $user;
    }

    public function testReturnsArrayOfDimensions(): void
    {
        $query = new UsersQuery($this::$entityManager);

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

    public function testReturnsValueIfNoDimension(): void
    {
        $query = new UsersQuery($this::$entityManager);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'users',
        ]));

        $this->assertCount(1, $result);
        $this->assertCount(1, $result[0]);
        $this->assertArrayHasKey('amount', $result[0]);
    }

    public function testAdheresToDateRange(): void
    {
        $query = new UsersQuery($this::$entityManager);

        $this->addUserWithRegistrationDate('2016-05-04');
        $this->addUserWithRegistrationDate('2016-11-27');

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'satisfaction',
            'startDate' => '2016-05-01',
            'endDate' => '2016-05-31',
        ]));

        $this->assertEquals(1, $result[0]['amount']);
    }

    public function testIncludesDataFromBothEndDays(): void
    {
        $query = new UsersQuery($this::$entityManager);

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

    public function ignoresDateRangeIfMetricNotConstrainedByDate(): void
    {
        $query = new UsersQuery($this::$entityManager);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'not-constrained',
            'startDate' => '2016-05-01',
            'endDate' => '2016-05-31',
        ]));

        $this->assertGreaterThanOrEqual(2, $result[0]['amount']);
    }
}
