<?php

namespace Tests\OPG\Digideps\Backend\Integration\Stats\Query;

use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;
use DateTime;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Service\Stats\Query\ClientsQuery;
use OPG\Digideps\Backend\Service\Stats\StatsQueryParameters;

class ClientsQueryIntegrationTest extends ApiIntegrationTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        static::givenClientWithReportsOfType(['102', '102']);
        static::givenClientWithReportsOfType(['103']);
        static::givenClientWithReportsOfType(['103']);
        static::givenClientWithReportsOfType(['104']);
        static::givenClientWithReportsOfType(['102-5']);
        static::givenClientWithReportsOfType(['102-5']);
        static::givenClientWithReportsOfType(['103-5']);
        static::givenClientWithReportsOfType(['102-6']);
        static::givenClientWithReportsOfType(['102-6']);
        static::givenClientWithReportsOfType(['103-6']);

        self::$entityManager->flush();
    }

    private static function givenClientWithReportsOfType(array $reportTypes): void
    {
        $client = new Client();
        foreach ($reportTypes as $reportType) {
            $report = new Report(
                $client,
                $reportType,
                new DateTime('2019-08-01'),
                new DateTime('2020-08-01')
            );

            self::$entityManager->persist($report);
        }

        self::$entityManager->persist($client);
    }

    public function testReturnsTotalClientsByDeputyType(): void
    {
        $query = new ClientsQuery(self::$entityManager);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'clients',
            'dimension' => ['deputyType'],
        ]));

        // Assert an array result for each deputy type submitted.
        $this->assertCount(3, $result);

        // Assert correct amount is returned for each deputy type.
        foreach ($result as $metric) {
            switch ($metric['deputyType']) {
                case 'lay':
                    $this->assertEquals(4, $metric['amount']);
                    break;
                case 'pa':
                case 'prof':
                    $this->assertEquals(3, $metric['amount']);
            }
        }
    }

    public function testReturnsTotalClientByReportType(): void
    {
        $query = new ClientsQuery(self::$entityManager);

        $result = $query->execute(new StatsQueryParameters([
            'metric' => 'clients',
            'dimension' => ['reportType'],
        ]));

        // Assert an array result for each report type submitted
        $this->assertCount(7, $result);

        // Assert correct amount is returned for each report type
        foreach ($result as $metric) {
            switch ($metric['reportType']) {
                case '103':
                case '102-6':
                case '102-5':
                    $this->assertEquals(2, $metric['amount']);
                    break;
                case '102':
                case '104':
                case '103-6':
                case '103-5':
                    $this->assertEquals(1, $metric['amount']);
            }
        }
    }
}
