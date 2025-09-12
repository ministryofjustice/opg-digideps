<?php

namespace App\Tests\Integration\Service\Stats\Query;

use App\Tests\Integration\ApiTestCase;
use DateTime;
use App\Entity\Client;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Entity\User;
use App\Service\Stats\Query\ReportsSubmittedQuery;
use App\Service\Stats\StatsQueryParameters;

class ReportsSubmittedQueryTest extends ApiTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::givenXreportSubmissionsOfTypeBelongToDeputy('4', '102', 'LAY');
        static::givenXreportSubmissionsOfTypeBelongToDeputy('2', '102', 'LAY');
        static::givenXreportSubmissionsOfTypeBelongToDeputy('2', '103', 'PA');
        static::givenXreportSubmissionsOfTypeBelongToDeputy('1', '102-6', 'PA');
        static::givenXreportSubmissionsOfTypeBelongToDeputy('2', '103-6', 'PA');
        static::givenXreportSubmissionsOfTypeBelongToDeputy('4', '102-5', 'PROF');
        static::givenXreportSubmissionsOfTypeBelongToDeputy('3', '103-5', 'PROF');

        self::$entityManager->flush();
    }

    private static function addSubmittedReportOfTypeToUser(string $type, User $user): void
    {
        $client = new Client();

        $report = new Report(
            $client,
            $type,
            new DateTime('2019-08-01'),
            new DateTime('2020-08-01')
        );

        $submission = new ReportSubmission($report, $user);

        self::$entityManager->persist($client);
        self::$entityManager->persist($report);
        self::$entityManager->persist($submission);
    }

    private static function givenXreportSubmissionsOfTypeBelongToDeputy($numReports, $reportType, $deputyType)
    {
        for ($i = 0; $i < $numReports; ++$i) {
            static::addSubmittedReportOfTypeToUser($reportType, static::createUserOfType($deputyType));
        }
    }

    private static function createUserOfType(string $type): User
    {
        $id = mt_rand();
        $user = (new User())
            ->setFirstname('Lay')
            ->setLastname('User')
            ->setEmail("metric-test-$id@publicguardian.gov.uk")
            ->setRoleName('ROLE_'.$type.'_DEPUTY');

        self::$entityManager->persist($user);

        return $user;
    }

    public function testReturnsTotalReportsSubmittedByDeputyType(): void
    {
        $query = new ReportsSubmittedQuery(self::$entityManager);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'reportsSubmitted',
            'dimension' => ['deputyType'],
        ]));

        // Assert an array result for each deputy type submitted.
        $this->assertCount(3, $result);

        // Assert correct amount is returned for each deputy type.
        foreach ($result as $metric) {
            switch ($metric['deputyType']) {
                case 'lay':
                    $this->assertEquals(6, $metric['amount']);
                    break;
                case 'pa':
                    $this->assertEquals(5, $metric['amount']);
                    break;
                case 'prof':
                    $this->assertEquals(7, $metric['amount']);
                    break;
            }
        }
    }

    public function testReturnsTotalReportsSubmittedByReportType(): void
    {
        $query = new ReportsSubmittedQuery(self::$entityManager);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'reportsSubmitted',
            'dimension' => ['reportType'],
        ]));

        // Assert an array result for each report type submitted
        $this->assertCount(6, $result);

        // Assert correct amount is returned for each report type
        foreach ($result as $metric) {
            switch ($metric['reportType']) {
                case '102':
                    $this->assertEquals(6, $metric['amount']);
                    break;
                case '103':
                case '103-6':
                    $this->assertEquals(2, $metric['amount']);
                    break;
                case '102-6':
                    $this->assertEquals(1, $metric['amount']);
                    break;
                case '102-5':
                    $this->assertEquals(4, $metric['amount']);
                    break;
                case '103-5':
                    $this->assertEquals(3, $metric['amount']);
            }
        }
    }
}
