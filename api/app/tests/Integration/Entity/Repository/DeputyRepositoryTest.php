<?php

namespace App\Tests\Integration\Entity\Repository;

use App\Entity\Deputy;
use App\Repository\DeputyRepository;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\CourtOrderTestHelper;
use App\TestHelpers\DeputyTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\TestHelpers\UserTestHelper;
use App\Tests\Integration\Fixtures;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DeputyRepositoryTest extends WebTestCase
{
    private static DeputyRepository $sut;
    private static EntityManagerInterface $em;
    private static Fixtures $fixtures;

    public static function setUpBeforeClass(): void
    {
        $container = static::getContainer();

        /** @var EntityManager $em */
        $em = $container->get(id: 'em');
        self::$em = $em;
        self::$fixtures = new Fixtures(em: self::$em);

        /** @var EntityRepository $sut */
        self::$sut = self::$em->getRepository(entityName: Deputy::class);

        $purger = new ORMPurger(em: self::$em);
        $purger->purge();
    }

    public function testFindReportsInfoByUid()
    {
        $deputyUid = '7000000021';
        $courtOrderUid = '7100000080';

        $deputy = DeputyTestHelper::generateDeputy(deputyUid: $deputyUid, em: self::$em);
        $client = ClientTestHelper::generateClient(em: self::$em);
        $user = UserTestHelper::createAndPersistUser(em: self::$em, client: $client, deputyUid: $deputyUid);
        $report = ReportTestHelper::generateReport(em: self::$em, client: $client);
        $courtOrder = CourtOrderTestHelper::generateCourtOrder(
            em: self::$em,
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
        $deputyUid = '70000022';
        $results = self::$sut->findReportsInfoByUid(uid: $deputyUid);

        self::assertNull($results);
    }

    public static function tearDownAfterClass(): void
    {
        $purger = new ORMPurger(em: self::$em);
        $purger->purge();
    }
}
