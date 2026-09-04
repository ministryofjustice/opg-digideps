<?php

namespace Tests\OPG\Digideps\Backend\Integration\Stats\Query;

use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\ReportList;
use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;
use OPG\Digideps\Backend\Service\Stats\Query\ClientsQuery;
use OPG\Digideps\Backend\Service\Stats\StatsQueryParameters;

class ClientsQueryIntegrationTest extends ApiIntegrationTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(
            DeputySet::oneLay(),
            CourtOrderReportType::OPG102,
            reportList: ReportList::manyReports()
        )), flush: false);
        self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(
            DeputySet::oneLay(),
            CourtOrderReportType::OPG103,
        )), flush: false);
        self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(
            DeputySet::oneLay(),
            CourtOrderReportType::OPG103,
        )), flush: false);
        self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(
            DeputySet::oneLay(),
            CourtOrderReportType::OPG104,
        )), flush: false);

        self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(
            DeputySet::oneNamedPro(),
            CourtOrderReportType::OPG102,
        )), flush: false);
        self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(
            DeputySet::oneNamedPro(),
            CourtOrderReportType::OPG102,
        )), flush: false);
        self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(
            DeputySet::oneNamedPro(),
            CourtOrderReportType::OPG103,
        )), flush: false);

        self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(
            DeputySet::oneNamedPa(),
            CourtOrderReportType::OPG102,
        )), flush: false);
        self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(
            DeputySet::oneNamedPa(),
            CourtOrderReportType::OPG102,
        )), flush: false);
        self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(
            DeputySet::oneNamedPa(),
            CourtOrderReportType::OPG103,
        )));
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
