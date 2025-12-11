<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\CourtOrderTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\v2\Service\CourtOrderService;
use App\TestHelpers\DeputyTestHelper;
use App\TestHelpers\UserTestHelper;
use App\Tests\Integration\ApiIntegrationTestCase;
use App\Tests\Integration\Fixtures;

class CourtOrderServiceIntegrationTest extends ApiIntegrationTestCase
{
    private static Fixtures $fixtures;
    private static CourtOrderService $sut;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$fixtures = new Fixtures(self::$entityManager);

        /** @var CourtOrderService $sut */
        $sut = self::$container->get(CourtOrderService::class);
        self::$sut = $sut;
    }

    public function testGetCourtOrderSingleReport(): void
    {
        $uid = '700999999999';
        $deputyUid = '700888888888';
        $email = 'deputy12345@example.org';

        $em = self::$entityManager;

        // User who will be authorised and Deputy tied to the user
        $user = (UserTestHelper::create())->createAndPersistUser($em, email: $email);
        $deputy = DeputyTestHelper::generateDeputy($email, $deputyUid, $user);
        $em->persist($deputy);

        $client = (ClientTestHelper::create())->generateClient($em, $user);
        $client->setFirstName('Alice');
        $client->setLastName('Example');
        $em->persist($client);

        $report = (ReportTestHelper::create())->generateReport($em, $client);
        $report->setStartDate(new \DateTime('2024-01-01'));
        $report->setEndDate(new \DateTime('2024-12-31'));
        $report->setSubmittedBy($user); // we set it to prove it changes to null
        $em->persist($report);

        $courtOrder = CourtOrderTestHelper::generateCourtOrder($em, $client, $uid, 'ACTIVE', 'pfa', $report, $deputy);
        $em->persist($courtOrder);

        $em->flush();

        // --- Act ---
        $result = self::$sut->getCourtOrderView($uid, $user);

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
        self::assertSame($email, $deputyUserRow['email'], 'Deputy embedded user email should match');

        // Client
        $clientRow = $result['client'];
        self::assertNotNull($clientRow, 'Client should be populated');
        self::assertIsArray($clientRow);
        self::assertArrayHasKey('firstname', $clientRow);
        self::assertArrayHasKey('lastname', $clientRow);
        self::assertSame('Alice', $clientRow['firstname']);
        self::assertSame('Example', $clientRow['lastname']);

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
            self::assertSame('2024-01-01', $reportRow['start_date'], 'Start date should be transformed to Y-m-d');
        }
        if (isset($reportRow['end_date'])) {
            self::assertSame('2024-12-31', $reportRow['end_date'], 'End date should be transformed to Y-m-d');
        }
    }

    public function testGetCourtOrderMultiReport(): void
    {
        $uid = '700666666666';
        $deputyUid = '700777777777';

        $em = self::$entityManager;
        $user = (UserTestHelper::create())->createAndPersistUser($em);
        $deputy = DeputyTestHelper::generateDeputy($email, $deputyUid, $user);
        $em->persist($deputy);
        $client = (ClientTestHelper::create())->generateClient($em, $user);
        $em->persist($client);
        $report1 = (ReportTestHelper::create())->generateReport($em, $client);
        $report2 = (ReportTestHelper::create())->generateReport($em, $client);
        $report3 = (ReportTestHelper::create())->generateReport($em, $client);
        $em->persist($report1);
        $em->persist($report2);
        $em->persist($report3);
        $courtOrder = CourtOrderTestHelper::generateCourtOrder($em, $client, $uid, 'ACTIVE', 'pfa', $report1, $deputy);
        $courtOrder->addReport($report2);
        $courtOrder->addReport($report3);
        $em->persist($courtOrder);
        $em->flush();

        $result = self::$sut->getCourtOrderView($uid, $user);

        self::assertIsArray($result['reports']);
        self::assertCount(3, $result['reports'], 'Expect exactly 3 reports');
    }

    public function testGetCourtOrderDifferentUser(): void
    {
        // Check authorisation works and doesn't bring back data if user not a deputy on case.
        $uid = '700444444444';
        $deputyUid = '700555555555';
        $email = 'deputy67890@example.org';

        $em = self::$entityManager;

        $userWhoIsDeputyOnReport = (UserTestHelper::create())->createAndPersistUser($em);
        $userWhoIsNotDeputyOnReport = (UserTestHelper::create())->createAndPersistUser($em);
        $deputy = DeputyTestHelper::generateDeputy($email, $deputyUid, $userWhoIsDeputyOnReport);
        $em->persist($deputy);
        $client = (ClientTestHelper::create())->generateClient($em, $userWhoIsDeputyOnReport);
        $em->persist($client);
        $report = (ReportTestHelper::create())->generateReport($em, $client);
        $em->persist($report);
        $courtOrder = CourtOrderTestHelper::generateCourtOrder($em, $client, $uid, 'ACTIVE', 'pfa', $report, $deputy);
        $em->persist($courtOrder);
        $em->flush();

        $result = self::$sut->getCourtOrderView($uid, $userWhoIsNotDeputyOnReport);

        self::assertNull($result, 'Expected court order view to be null');
    }
}
