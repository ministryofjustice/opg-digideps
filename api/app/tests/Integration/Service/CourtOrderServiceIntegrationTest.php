<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Integration\Service;

use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderService;
use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputyDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\ReportList;
use OPG\Digideps\Backend\Fixture\Scenario;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;

class CourtOrderServiceIntegrationTest extends ApiIntegrationTestCase
{
    private static CourtOrderService $sut;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        /** @var CourtOrderService $sut */
        $sut = self::$container->get(CourtOrderService::class);
        self::$sut = $sut;
    }

    public function testGetCourtOrderSingleReport(): void
    {
        [
            'client' => $client,
            'persons' => ['users' => ['lay1' => $user], 'deputies' => ['lay1' => $deputy]],
            'orders' => [['pfa' => ['order' => $courtOrder, 'reports' => [$report]]]]
        ] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());

        // --- Act ---
        $result = self::$sut->getCourtOrderData($courtOrder->getCourtOrderUid(), $user);

        // --- Assert ---
        self::assertNotNull($result, 'Expected court order view not to be null');

        // Top-level keys exist
        self::assertArrayHasKey('active_deputies', $result);
        self::assertArrayHasKey('client', $result);
        self::assertArrayHasKey('reports', $result);

        // Active deputies
        self::assertIsArray($result['active_deputies']);
        self::assertCount(1, $result['active_deputies'], 'Should include exactly 1 active deputy');

        $deputyRow = $result['active_deputies'][0];
        self::assertIsArray($deputyRow);
        self::assertArrayHasKey('user', $deputyRow, 'Deputy row should include embedded user details');
        self::assertNotNull($deputyRow['user']);

        $deputyUserRow = $deputyRow['user'];
        self::assertIsArray($deputyUserRow);
        self::assertArrayHasKey('email', $deputyUserRow);
        self::assertSame($deputy->getEmail1(), $deputyUserRow['email'], 'Deputy embedded user email should match');

        // Client
        $clientRow = $result['client'];
        self::assertNotNull($clientRow, 'Client should be populated');
        self::assertIsArray($clientRow);
        self::assertArrayHasKey('firstname', $clientRow);
        self::assertArrayHasKey('lastname', $clientRow);
        self::assertSame($client->getFirstname(), $clientRow['firstname']);
        self::assertSame($client->getLastname(), $clientRow['lastname']);

        // Reports
        self::assertIsArray($result['reports']);
        self::assertCount(1, $result['reports'], 'Expect exactly 1 report');
        $reportRow = $result['reports'][0];

        // Status mapping: ['status']['status'] comes from 'report_status_cached'
        self::assertArrayHasKey('status', $reportRow);
        self::assertIsArray($reportRow['status']);
        self::assertArrayHasKey('status', $reportRow['status']);
        self::assertSame(
            'notStarted',
            $reportRow['status']['status'],
            'Report status should be mapped from report_status_cached'
        );

        // Submitted_by is intentionally null
        self::assertArrayHasKey('submitted_by', $reportRow);
        self::assertNull($reportRow['submitted_by']);

        if (isset($reportRow['start_date'])) {
            self::assertSame($report->getStartDate()->format('Y-m-d'), $reportRow['start_date'], 'Start date should be transformed to Y-m-d');
        }
        if (isset($reportRow['end_date'])) {
            self::assertSame($report->getEndDate()->format('Y-m-d'), $reportRow['end_date'], 'End date should be transformed to Y-m-d');
        }
    }

    // Check that we get right number of reports and deputies for 3 reports and 2 active deputies and 1 inactive
    public function testGetCourtOrderMultiReportMultiDeputy(): void
    {
        [
            'persons' => ['users' => ['lay1' => $user]],
            'orders' => [['pfa' => ['order' => $courtOrder]]]
        ] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(
            new DeputySet(
                new DeputyDescriptor('lay1'),
                new DeputyDescriptor('lay2'),
                new DeputyDescriptor('lay3', isActive: false),
            ),
            reportList: ReportList::manyReports(submittedReports: 2)
        )));

        $result = self::$sut->getCourtOrderData($courtOrder->getCourtOrderUid(), $user);

        self::assertIsArray($result['reports']);
        self::assertCount(3, $result['reports'], 'Expect exactly 3 reports');
        self::assertIsArray($result['active_deputies']);
        self::assertCount(2, $result['active_deputies'], 'Expect exactly 2 active deputies');
    }

    public function testGetCourtOrderDifferentUser(): void
    {
        ['orders' => [['pfa' => ['order' => $courtOrder]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());
        ['persons' => ['users' => ['lay1' => $user]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());

        $result = self::$sut->getCourtOrderData($courtOrder->getCourtOrderUid(), $user);

        self::assertNull($result, 'Expected court order view to be null');
    }
}
