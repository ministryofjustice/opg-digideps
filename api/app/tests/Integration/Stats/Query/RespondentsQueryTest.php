<?php

namespace App\Tests\Integration\Service\Stats\Query;

use App\Entity\Client;
use App\Entity\Report\Report;
use App\Entity\Satisfaction;
use App\Service\Stats\Query\RespondentsQuery;
use App\Service\Stats\StatsQueryParameters;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RespondentsQueryTest extends WebTestCase
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
        static::givenSatisfactionScoreForReportOfTypeAndRole(1);
        static::givenSatisfactionScoreForReportOfTypeAndRole(2);
        static::givenSatisfactionScoreForReportOfTypeAndRole(3);
        static::givenSatisfactionScoreForReportOfTypeAndRole(4, '102', 'LAY');
        static::givenSatisfactionScoreForReportOfTypeAndRole(5, '104', 'LAY');

        self::$em->flush();
    }

    public function testReturnsNumberOfRespondents()
    {
        $query = new RespondentsQuery($this::$em);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'respondents',
        ]));

        $this->assertCount(1, $result);
        $this->assertEquals(2, $result[0]['amount']);
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
