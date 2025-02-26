<?php

namespace App\Tests\Integration\Service\Stats\Query;

use App\Entity\Client;
use App\Entity\Report\Report;
use App\Entity\Satisfaction;
use App\Service\Stats\Query\SatisfactionQuery;
use App\Service\Stats\StatsQueryParameters;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SatisfactionQueryTest extends WebTestCase
{
    /** @var EntityManager */
    protected static $em;

    public static function setUpBeforeClass(): void
    {
        $kernel = self::bootKernel(['environment' => 'test', 'debug' => false]);

        self::$em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        // Clear up old data
        $scores = self::$em
            ->getRepository(Satisfaction::class)
            ->findAll();

        foreach ($scores as $score) {
            self::$em->remove($score);
        }

        // Add test data
        static::givenSatisfactionScoreForReportOfTypeAndRole(3, '102', 'LAY');
        static::givenSatisfactionScoreForReportOfTypeAndRole(4, '102', 'LAY');
        static::givenSatisfactionScoreForReportOfTypeAndRole(5, '103', 'LAY');
        static::givenSatisfactionScoreForReportOfTypeAndRole(4, '103', 'LAY');
        static::givenSatisfactionScoreForReportOfTypeAndRole(4, '103-6', 'PA');
        static::givenSatisfactionScoreForReportOfTypeAndRole(4, '103-6', 'PA');
        static::givenSatisfactionScoreForReportOfTypeAndRole(2, '102-6', 'PA');
        static::givenSatisfactionScoreForReportOfTypeAndRole(2, '102-6', 'PA');
        static::givenSatisfactionScoreForReportOfTypeAndRole(5, '102-5', 'PROF');
        static::givenSatisfactionScoreForReportOfTypeAndRole(3, '102-5', 'PROF');
        static::givenSatisfactionScoreForReportOfTypeAndRole(3, '103-5', 'PROF');
        static::givenSatisfactionScoreForReportOfTypeAndRole(3, '103-5', 'PROF');

        static::givenSatisfactionScoreForReportOfTypeAndRole(1);
        static::givenSatisfactionScoreForReportOfTypeAndRole(1);
        static::givenSatisfactionScoreForReportOfTypeAndRole(3);

        self::$em->flush();
    }

    public function testReturnsOverallSatisfaction()
    {
        $query = new SatisfactionQuery($this::$em);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'satisfaction',
        ]));

        $this->assertCount(1, $result);
        $this->assertEquals(63, $result[0]['amount']);
    }

    public function testReturnsSatisfactionAverageByDeputyType()
    {
        $query = new SatisfactionQuery($this::$em);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'satisfaction',
            'dimension' => ['deputyType'],
        ]));

        // Assert an array result for each deputy type submitted.
        $this->assertCount(3, $result);

        // Assert correct amount is returned for each deputy type.
        foreach ($result as $metric) {
            switch ($metric['deputyType']) {
                case 'lay':
                    $this->assertEquals(75, $metric['amount']);
                    break;
                case 'pa':
                    $this->assertEquals(50, $metric['amount']);
                    break;
                case 'prof':
                    $this->assertEquals(63, $metric['amount']);
                    break;
            }
        }
    }

    public function testReturnsSatisfactionAverageByReportType()
    {
        $query = new SatisfactionQuery($this::$em);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'satisfaction',
            'dimension' => ['reportType'],
        ]));

        // Assert an array result for each report type submitted
        $this->assertCount(6, $result);

        // Assert correct amount is returned for each report type
        foreach ($result as $metric) {
            switch ($metric['reportType']) {
                case '102':
                    $this->assertEquals(63, $metric['amount']);
                    break;
                case '103':
                    $this->assertEquals(88, $metric['amount']);
                    break;
                case '102-6':
                    $this->assertEquals(25, $metric['amount']);
                    break;
                case '103-6':
                    $this->assertEquals(75, $metric['amount']);
                    break;
                case '102-5':
                    $this->assertEquals(75, $metric['amount']);
                    break;
                case '103-5':
                    $this->assertEquals(50, $metric['amount']);
                    break;
            }
        }
    }

    private static function givenSatisfactionScoreForReportOfTypeAndRole($score, $reportType = null, $deputyType = null)
    {
        $satisfaction = (new Satisfaction())
            ->setScore($score);

        if (isset($reportType)) {
            $client = new Client();

            $report = new Report(
                $client,
                $reportType,
                new \DateTime('2019-08-01'),
                new \DateTime('2020-08-01')
            );
            self::$em->persist($client);
            self::$em->persist($report);

            $satisfaction->setReportType($reportType);
            $satisfaction->setReport($report);
        }

        if (isset($deputyType)) {
            $satisfaction->setDeputyRole('ROLE_'.$deputyType.'_DEPUTY');
        }

        self::$em->persist($satisfaction);
    }
}
