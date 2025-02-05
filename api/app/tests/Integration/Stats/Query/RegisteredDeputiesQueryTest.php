<?php

namespace App\Tests\Unit\Service\Stats\Query;

use App\Entity\User;
use App\Service\Stats\Query\RegisteredDeputiesQuery;
use App\Service\Stats\StatsQueryParameters;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisteredDeputiesQueryTest extends WebTestCase
{
    /** @var EntityManager */
    protected static $em;

    public static function setUpBeforeClass(): void
    {
        $kernel = self::bootKernel(['environment' => 'test', 'debug' => false]);

        self::$em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        // Remove existing test data
        $deputies = self::$em
            ->getRepository(User::class)
            ->findAll();

        foreach ($deputies as $deputy) {
            self::$em->remove($deputy);
        }

        // Add test data
        static::givenUsersExistWithRole(4, 'ROLE_PA_TEST');
        static::givenUsersExistWithRole(2, 'ROLE_PROF_TEST');
        static::givenUsersExistWithRole(7, 'ROLE_LAY_DEPUTY');

        self::$em->flush();
    }

    public function testReturnsDeputiesByType()
    {
        $query = new RegisteredDeputiesQuery($this::$em);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'registeredDeputies',
            'dimension' => ['deputyType'],
        ]));

        // Assert an array result for each deputy type submitted.
        $this->assertCount(3, $result);

        // Assert correct amount is returned for each deputy type.
        foreach ($result as $metric) {
            switch ($metric['deputyType']) {
                case 'lay':
                    $this->assertEquals(7, $metric['amount']);
                    break;
                case 'pa':
                    $this->assertEquals(4, $metric['amount']);
                    break;
                case 'prof':
                    $this->assertEquals(2, $metric['amount']);
                    break;
            }
        }
    }

    public function testReturnsDeputiesCollated()
    {
        $query = new RegisteredDeputiesQuery($this::$em);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'registeredDeputies',
        ]));

        // Assert an array result for each deputy type submitted.
        $this->assertCount(1, $result);

        // Assert correct amount is returned
        $this->assertEquals(13, $result[0]['amount']);
    }

    public function testAdheresToDates()
    {
        $query = new RegisteredDeputiesQuery($this::$em);

        $twoWeeksAgo = (new \DateTime('-14 days'))->format('Y-m-d');
        $oneWeeksAgo = (new \DateTime('-7 days'))->format('Y-m-d');
        $today = (new \DateTime())->format('Y-m-d');

        $resultOutOfRange = $query->execute(new StatsQueryParameters([
            'metric' => 'registeredDeputies',
            'startDate' => $twoWeeksAgo,
            'endDate' => $oneWeeksAgo,
        ]));

        $this->assertEquals(0, $resultOutOfRange[0]['amount']);

        $resultInRange = $query->execute(new StatsQueryParameters([
            'metric' => 'registeredDeputies',
            'startDate' => $twoWeeksAgo,
            'endDate' => $today,
        ]));

        $this->assertEquals(13, $resultInRange[0]['amount']);
    }

    private static function givenUsersExistWithRole($count, $roleName)
    {
        for ($i = 0; $i < $count; ++$i) {
            $id = md5(microtime());
            $user = (new User())
                ->setFirstname('Test')
                ->setLastname('User')
                ->setEmail("test-user-$id@example.com")
                ->setRegistrationDate(new \DateTime())
                ->setRoleName($roleName);

            self::$em->persist($user);
        }
    }
}
