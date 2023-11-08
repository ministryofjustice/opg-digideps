<?php

namespace App\Tests\Unit\Service\Stats\Query;

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
        static::givenSatisfactionScoreForReportOfTypeAndRole(4);
        static::givenSatisfactionScoreForReportOfTypeAndRole(5);

        self::$em->flush();
    }

    public function testReturnsNumberOfRespondents()
    {
        $query = new RespondentsQuery($this::$em);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'respondents',
        ]));

        $this->assertCount(1, $result);
        $this->assertEquals(5, $result[0]['amount']);
    }

    private static function givenSatisfactionScoreForReportOfTypeAndRole($score, $reportType = null, $deputyType = null)
    {
        $satisfaction = (new Satisfaction())
            ->setScore($score);

        if (isset($reportType)) {
            $satisfaction->setReportType($reportType);
        }

        if (isset($deputyType)) {
            $satisfaction->setDeputyRole('ROLE_'.$deputyType.'_DEPUTY');
        }

        self::$em->persist($satisfaction);
    }
}
