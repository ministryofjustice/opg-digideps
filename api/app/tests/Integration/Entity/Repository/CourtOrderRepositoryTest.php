<?php

declare(strict_types=1);

namespace App\Tests\Integration\Entity\Repository;

use App\Entity\CourtOrder;
use App\Repository\CourtOrderRepository;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\CourtOrderTestHelper;
use App\TestHelpers\DeputyTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\Tests\Integration\ApiIntegrationTestCase;

class CourtOrderRepositoryTest extends ApiIntegrationTestCase
{
    private static CourtOrderRepository $sut;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var CourtOrderRepository $repo */
        $repo = self::$entityManager->getRepository(CourtOrder::class);
        self::$sut = $repo;
    }

    // reproduces bug in DDLS-1124
    public function testFindReportsInfoByUidReportOnMultipleCourtOrdersOneActiveOneInactive(): void
    {
        $deputyUid = '23845738';
        $activeCoUid = '1119485732';
        $closedCoUid = '2229485732';

        $deputy = DeputyTestHelper::generateDeputy(deputyUid: $deputyUid);
        self::$entityManager->persist($deputy);

        $client = ClientTestHelper::generateClient(self::$entityManager, caseNumber: '224123523');
        self::$entityManager->persist($client);

        $report = ReportTestHelper::generateReport(self::$entityManager, dateChecks: false);

        // first active court order
        $courtOrder1 = CourtOrderTestHelper::generateCourtOrder(
            self::$entityManager,
            $client,
            $activeCoUid,
            report: $report,
            deputy: $deputy,
        );

        // second closed court order attached to the same report
        $courtOrder2 = CourtOrderTestHelper::generateCourtOrder(
            self::$entityManager,
            $client,
            $closedCoUid,
            'CLOSED',
            report: $report,
            deputy: $deputy,
        );

        self::$entityManager->persist($courtOrder1);
        self::$entityManager->persist($courtOrder2);

        self::$entityManager->flush();

        $result = self::$sut->findReportsInfoByUid($deputyUid);

        self::assertCount(1, $result);
        self::assertStringContainsString($activeCoUid, $result[0]['courtOrderUid']);
        self::assertStringNotContainsString($closedCoUid, $result[0]['courtOrderUid']);
    }

    public function testFindReportsInfoByUidReportOnMultipleCourtOrdersTwoActive(): void
    {
        $deputyUid = '258477389';
        $activeCoUid1 = '4449485733';
        $activeCoUid2 = '5559485733';

        $deputy = DeputyTestHelper::generateDeputy(deputyUid: $deputyUid);
        self::$entityManager->persist($deputy);

        $client = ClientTestHelper::generateClient(self::$entityManager, caseNumber: '224123523');
        self::$entityManager->persist($client);

        $report = ReportTestHelper::generateReport(self::$entityManager, dateChecks: false);

        // first active court order
        $courtOrder1 = CourtOrderTestHelper::generateCourtOrder(
            self::$entityManager,
            $client,
            $activeCoUid1,
            report: $report,
            deputy: $deputy,
        );

        // second active court order attached to the same report
        $courtOrder2 = CourtOrderTestHelper::generateCourtOrder(
            self::$entityManager,
            $client,
            $activeCoUid2,
            report: $report,
            deputy: $deputy,
        );

        self::$entityManager->persist($courtOrder1);
        self::$entityManager->persist($courtOrder2);

        self::$entityManager->flush();

        $result = self::$sut->findReportsInfoByUid($deputyUid);

        self::assertCount(1, $result);
        self::assertStringContainsString($activeCoUid1, $result[0]['courtOrderUid']);
        self::assertStringContainsString($activeCoUid2, $result[0]['courtOrderUid']);
    }
}
