<?php

namespace Tests\OPG\Digideps\Backend\Integration\Stats\Query;

use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;
use DateTime;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\Satisfaction;
use OPG\Digideps\Backend\Service\Stats\Query\RespondentsQuery;
use OPG\Digideps\Backend\Service\Stats\StatsQueryParameters;

class RespondentsQueryIntegrationTest extends ApiIntegrationTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Clear up old data
        $scores = self::$entityManager->getRepository(Satisfaction::class)->findAll();

        foreach ($scores as $score) {
            self::$entityManager->remove($score);
        }

        self::$entityManager->flush();

        // Add test data
        static::givenSatisfactionScoreForReportOfTypeAndRole(1);
        static::givenSatisfactionScoreForReportOfTypeAndRole(2);
        static::givenSatisfactionScoreForReportOfTypeAndRole(3);
        static::givenSatisfactionScoreForReportOfTypeAndRole(4, '102', 'LAY');
        static::givenSatisfactionScoreForReportOfTypeAndRole(5, '104', 'LAY');

        self::$entityManager->flush();
    }

    private static function givenSatisfactionScoreForReportOfTypeAndRole(
        int $score,
        ?string $reportType = null,
        ?string $deputyType = null
    ): void {
        $satisfaction = new Satisfaction()->setScore($score);

        if (isset($reportType)) {
            $client = new Client();

            $report = new Report(
                $client,
                $reportType,
                new DateTime('2019-08-01'),
                new DateTime('2020-08-01')
            );
            self::$entityManager->persist($client);
            self::$entityManager->persist($report);

            $satisfaction->setReportType($reportType);
            $satisfaction->setReport($report);
        }

        if (isset($deputyType)) {
            $satisfaction->setDeputyRole('ROLE_' . $deputyType . '_DEPUTY');
        }

        self::$entityManager->persist($satisfaction);
    }

    public function testReturnsNumberOfRespondents(): void
    {
        $query = new RespondentsQuery(self::$entityManager);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'respondents',
        ]));

        $this->assertCount(1, $result);
        $this->assertEquals(2, $result[0]['amount']);
    }
}
