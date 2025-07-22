<?php

declare(strict_types=1);

namespace App\Tests\Integration\Entity\Repository;

use App\Entity\Deputy;
use App\Repository\DeputyRepository;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\CourtOrderTestHelper;
use App\TestHelpers\DeputyTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\TestHelpers\UserTestHelper;
use App\Tests\Integration\ApiBaseTestCase;
use App\Tests\Integration\Fixtures;

class DeputyRepositoryTest extends ApiBaseTestCase
{
    private static DeputyRepository $sut;
    private static Fixtures $fixtures;

    public function setUp(): void
    {
        parent::setUp();

        self::$fixtures = new Fixtures(em: $this->entityManager);

        /** @var DeputyRepository $sut */
        $sut = $this->entityManager->getRepository(Deputy::class);
        self::$sut = $sut;
    }

    public function testFindReportsInfoByUid()
    {
        $deputyUid = 7000000021;
        $courtOrderUid = '7100000080';

        $deputy = DeputyTestHelper::generateDeputy(deputyUid: "$deputyUid");
        $client = ClientTestHelper::generateClient(em: $this->entityManager);
        $user = UserTestHelper::createAndPersistUser(em: $this->entityManager, client: $client, deputyUid: $deputyUid);
        $report = ReportTestHelper::generateReport(em: $this->entityManager, client: $client);
        $courtOrder = CourtOrderTestHelper::generateCourtOrder(
            em: $this->entityManager,
            client: $client,
            courtOrderUid: $courtOrderUid,
            report: $report,
            deputy: $deputy,
        );

        $deputy->setUser(user: $user);
        $client->setDeputy(deputy: $deputy);

        self::$fixtures->persist($deputy, $client);
        self::$fixtures->flush();

        $results = self::$sut->findReportsInfoByUid(uid: $deputyUid);

        self::assertCount(1, $results);
        self::assertArrayHasKey('client', $results[0]);

        self::assertArrayHasKey('firstName', $results[0]['client']);
        self::assertEquals($client->getFirstName(), $results[0]['client']['firstName']);

        self::assertArrayHasKey('lastName', $results[0]['client']);
        self::assertEquals($client->getLastName(), $results[0]['client']['lastName']);

        self::assertArrayHasKey('caseNumber', $results[0]['client']);
        self::assertEquals($client->getCaseNumber(), $results[0]['client']['caseNumber']);

        self::assertArrayHasKey('courtOrder', $results[0]);
        self::assertArrayHasKey('courtOrderUid', $results[0]['courtOrder']);
        self::assertEquals($courtOrder->getCourtOrderUid(), $results[0]['courtOrder']['courtOrderUid']);

        self::assertArrayHasKey('report', $results[0]);
        self::assertArrayHasKey('type', $results[0]['report']);
        self::assertEquals($report->getType(), $results[0]['report']['type']);
    }

    public function testFindReportsInfoByUidIsNull()
    {
        $deputyUid = 70000022;
        $results = self::$sut->findReportsInfoByUid(uid: $deputyUid);

        self::assertNull($results);
    }
}
