<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Service\DeputyService;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\CourtOrderTestHelper;
use App\TestHelpers\DeputyTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\TestHelpers\UserTestHelper;
use App\Tests\Integration\ApiIntegrationTestCase;
use App\Tests\Integration\Fixtures;

class DeputyServiceIntegrationTest extends ApiIntegrationTestCase
{
    private static Fixtures $fixtures;
    private static DeputyService $sut;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$fixtures = new Fixtures(self::$entityManager);

        /** @var DeputyService $sut */
        $sut = self::$container->get(DeputyService::class);
        self::$sut = $sut;
    }

    public function testFindReportsInfoByUidSuccess()
    {
        $deputyUid = 7000000021;
        $courtOrderUid = '7100000080';

        $client = ClientTestHelper::generateClient(em: self::$entityManager);
        $user = UserTestHelper::createAndPersistUser(em: self::$entityManager, client: $client, deputyUid: $deputyUid);
        $report = ReportTestHelper::generateReport(em: self::$entityManager, client: $client);
        $deputy = DeputyTestHelper::generateDeputy(deputyUid: "$deputyUid", user: $user);
        $client->setDeputy(deputy: $deputy);

        self::$fixtures->persist($deputy, $client);
        self::$fixtures->flush();

        $courtOrder = CourtOrderTestHelper::generateCourtOrder(
            em: self::$entityManager,
            client: $client,
            courtOrderUid: $courtOrderUid,
            report: $report,
            deputy: $deputy,
        );

        $results = self::$sut->findReportsInfoByUid(uid: "$deputyUid");

        self::assertCount(1, $results);
        self::assertArrayHasKey('client', $results[0]);

        self::assertArrayHasKey('firstName', $results[0]['client']);
        self::assertEquals($client->getFirstName(), $results[0]['client']['firstName']);

        self::assertArrayHasKey('lastName', $results[0]['client']);
        self::assertEquals($client->getLastName(), $results[0]['client']['lastName']);

        self::assertArrayHasKey('caseNumber', $results[0]['client']);
        self::assertEquals($client->getCaseNumber(), $results[0]['client']['caseNumber']);

        self::assertArrayHasKey('courtOrderUids', $results[0]);
        self::assertEquals([$courtOrder->getCourtOrderUid()], $results[0]['courtOrderUids']);

        self::assertArrayHasKey('courtOrderLink', $results[0]);
        self::assertEquals($courtOrder->getCourtOrderUid(), $results[0]['courtOrderLink']);

        self::assertArrayHasKey('report', $results[0]);
        self::assertArrayHasKey('type', $results[0]['report']);
        self::assertEquals($report->getType(), $results[0]['report']['type']);
    }

//    TODO - Fix this test
//    public function testFindReportsInfoByUidDeputyNotActiveOnOrder()
//    {
//        $deputyUid = 7000000022;
//        $courtOrderUid = '7100000081';
//
//        $client = ClientTestHelper::generateClient(em: self::$entityManager);
//        $user = UserTestHelper::createAndPersistUser(em: self::$entityManager, client: $client, deputyUid: $deputyUid);
//        $report = ReportTestHelper::generateReport(em: self::$entityManager, client: $client);
//        $deputy = DeputyTestHelper::generateDeputy(deputyUid: "$deputyUid", user: $user);
//        $client->setDeputy(deputy: $deputy);
//
//        self::$fixtures->persist($deputy, $client);
//        self::$fixtures->flush();
//
//        // active court order saved to database, but deputy is not active on the order
//        CourtOrderTestHelper::generateCourtOrder(
//            em: self::$entityManager,
//            client: $client,
//            courtOrderUid: $courtOrderUid,
//            report: $report,
//            deputy: $deputy,
//            deputyIsActive: false,
//        );
//
//        $results = self::$sut->findReportsInfoByUid(uid: "$deputyUid");
//
//        self::assertEquals([], $results);
//    }

    public function testFindReportsInfoByUidForNonExistentDeputyIsNull()
    {
        $results = self::$sut->findReportsInfoByUid(uid: '70000022');

        self::assertEquals(null, $results);
    }

    public function testFindReportsInfoByUidUsesLatestReportType(): void
    {
        $deputyUid = 7000000023;
        $courtOrderUid = '7100000082';

        $client = ClientTestHelper::generateClient(em: self::$entityManager);
        $user = UserTestHelper::createAndPersistUser(em: self::$entityManager, client: $client, deputyUid: $deputyUid);
        $deputy = DeputyTestHelper::generateDeputy(deputyUid: "$deputyUid", user: $user);
        $client->setDeputy(deputy: $deputy);

        self::$fixtures->persist($deputy, $client);
        self::$fixtures->flush();

        // active court order saved to database
        $courtOrder = CourtOrderTestHelper::generateCourtOrder(
            em: self::$entityManager,
            client: $client,
            courtOrderUid: $courtOrderUid,
            deputy: $deputy,
        );

        // two reports, one the current report and the other historical;
        // check that the most recent report's type is used as the type for the court order
        // (as displayed on the choose a court order page)
        $currentStart = new \DateTime();
        $currentReport = ReportTestHelper::generateReport(em: self::$entityManager, client: $client, type: '102', startDate: $currentStart);

        $oldStart = $currentStart->sub(new \DateInterval('P2Y'));
        $oldReport = ReportTestHelper::generateReport(em: self::$entityManager, client: $client, type: '103', startDate: $oldStart);

        $courtOrder->addReport($currentReport);
        $courtOrder->addReport($oldReport);

        self::$fixtures->persist($currentReport, $oldReport, $courtOrder);
        self::$fixtures->flush();

        $results = self::$sut->findReportsInfoByUid(uid: "$deputyUid");

        self::assertCount(1, $results);
        self::assertEquals('102', $results[0]['report']['type']);
    }

    // if there are two court orders for the same report, they display as a single item
    public function testFindReportsInfoByUidCombinesCourtOrders(): void
    {
        $deputyUid = 7000000024;
        $courtOrderUid1 = '7100000083';
        $courtOrderUid2 = '7100000084';

        $client = ClientTestHelper::generateClient(em: self::$entityManager);
        $user = UserTestHelper::createAndPersistUser(em: self::$entityManager, client: $client, deputyUid: $deputyUid);
        $deputy = DeputyTestHelper::generateDeputy(deputyUid: "$deputyUid", user: $user);
        $client->setDeputy(deputy: $deputy);

        self::$fixtures->persist($deputy, $client);
        self::$fixtures->flush();

        // two active court orders
        $courtOrder1 = CourtOrderTestHelper::generateCourtOrder(
            em: self::$entityManager,
            client: $client,
            courtOrderUid: $courtOrderUid1,
            deputy: $deputy,
        );

        $courtOrder2 = CourtOrderTestHelper::generateCourtOrder(
            em: self::$entityManager,
            client: $client,
            courtOrderUid: $courtOrderUid2,
            type: 'hw',
            deputy: $deputy,
        );

        // one hybrid report associated with both court orders
        $report = ReportTestHelper::generateReport(em: self::$entityManager, client: $client, type: '102-4');
        $courtOrder1->addReport($report);
        $courtOrder2->addReport($report);

        self::$fixtures->persist($report, $courtOrder1, $courtOrder2);
        self::$fixtures->flush();

        $results = self::$sut->findReportsInfoByUid(uid: "$deputyUid");

        self::assertCount(1, $results);
        self::assertEquals([$courtOrderUid1, $courtOrderUid2], $results[0]['courtOrderUids']);
        self::assertEquals('102-4', $results[0]['report']['type']);
    }
}
