<?php

namespace Tests\OPG\Digideps\Backend\Integration\Stats\Query;

use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Common\Deputy\DeputyType;
use OPG\Digideps\Common\Report\ReportType;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;
use OPG\Digideps\Backend\Entity\Satisfaction;
use OPG\Digideps\Backend\Service\Stats\Query\SatisfactionQuery;
use OPG\Digideps\Backend\Service\Stats\StatsQueryParameters;

class SatisfactionQueryIntegrationTest extends ApiIntegrationTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Clear up old data
        $scores = self::$entityManager
            ->getRepository(Satisfaction::class)
            ->findAll();

        foreach ($scores as $score) {
            self::$entityManager->remove($score);
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

        self::$entityManager->flush();
    }

    private static function givenSatisfactionScoreForReportOfTypeAndRole(
        int $score,
        ?string $reportType = null,
        ?string $deputyType = null
    ): void {
        $satisfaction = new Satisfaction($score);

        if (isset($reportType)) {
            $type = ReportType::from($reportType);
            ['orders' => [$pair]] = self::$fixtureService->instantiateScenario(match ($type->deputyType) {
                DeputyType::LAY => Scenario::newSimpleLayScenario(reportType: $type->courtOrderReportType),
                DeputyType::PA => Scenario::newSimplePaScenario(reportType: $type->courtOrderReportType),
                DeputyType::PRO => Scenario::newSimpleProScenario(reportType: $type->courtOrderReportType),
            });
            $report = $pair[$type->courtOrderType->value]['reports'][0];

            $satisfaction->setReportType($report->getType());
            $satisfaction->setReport($report);
        }

        if (isset($deputyType)) {
            $satisfaction->setDeputyRole('ROLE_' . $deputyType . '_DEPUTY');
        }

        self::$entityManager->persist($satisfaction);
    }

    public function testReturnsOverallSatisfaction(): void
    {
        $query = new SatisfactionQuery(self::$entityManager);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'satisfaction',
        ]));

        $this->assertCount(1, $result);
        $this->assertEquals(63, $result[0]['amount']);
    }

    public function testReturnsSatisfactionAverageByDeputyType(): void
    {
        $query = new SatisfactionQuery(self::$entityManager);

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

    public function testReturnsSatisfactionAverageByReportType(): void
    {
        $query = new SatisfactionQuery(self::$entityManager);

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
                case '102-5':
                    $this->assertEquals(75, $metric['amount']);
                    break;
                case '103-5':
                    $this->assertEquals(50, $metric['amount']);
                    break;
            }
        }
    }
}
