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

    public static function tearDownAfterClass(): void
    {
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

    public function testFindReportsInfoByUidDeputyNotActiveOnOrder()
    {
        $deputyUid = 7000000022;
        $courtOrderUid = '7100000081';

        $client = ClientTestHelper::generateClient(em: self::$entityManager);
        $user = UserTestHelper::createAndPersistUser(em: self::$entityManager, client: $client, deputyUid: $deputyUid);
        $report = ReportTestHelper::generateReport(em: self::$entityManager, client: $client);
        $deputy = DeputyTestHelper::generateDeputy(deputyUid: "$deputyUid", user: $user);

        $client->setDeputy(deputy: $deputy);

        self::$fixtures->persist($deputy, $client);
        self::$fixtures->flush();

        // active court order saved to database, but deputy is not active on the order
        CourtOrderTestHelper::generateCourtOrder(
            em: self::$entityManager,
            client: $client,
            courtOrderUid: $courtOrderUid,
            report: $report,
            deputy: $deputy,
            isActive: false,
        );

        $results = self::$sut->findReportsInfoByUid(uid: "$deputyUid");

        self::assertEquals([], $results);
    }

    public function testFindReportsInfoByUidForNonExistentDeputyIsNull()
    {
        $results = self::$sut->findReportsInfoByUid(uid: '70000022');

        self::assertEquals(null, $results);
    }
}
