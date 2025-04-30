<?php

namespace App\Repository;

use App\Entity\Deputy;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\DeputyTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\TestHelpers\UserTestHelper;
use App\Tests\Integration\Fixtures;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use function PHPUnit\Framework\assertEquals;

class DeputyRepositoryTest extends WebTestCase
{
    private DeputyRepository $sut;
    private EntityManagerInterface $em;
    
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->em = $kernel->getContainer()->get('doctrine')->getManager();
        $this->fixtures = new Fixtures(em: $this->em);

        $this->sut = $this->em->getRepository(entityName: Deputy::class);

        $purger = new ORMPurger(em: $this->em);
        $purger->purge();
    }

    public function testFindReportsInfoByUid()
    {
        $deputyUid = '70000021';
        $courtOrderUid = '71000080';

        $client = ClientTestHelper::generateClient(em: $this->em);
        $user = UserTestHelper::createAndPersistUser(em: $this->em, client: $client, deputyUid: $deputyUid);
        
        $deputy = DeputyTestHelper::generateDeputy(deputyUid: $deputyUid, user: $user);
        $this->em->persist(entity: $deputy);
        
        $report = ReportTestHelper::generateReport(em: $this->em, client: $client);
        $this->em->persist(entity: $report);

        $courtOrder = CourtOrderTestHelper::generateCourtOrder(
            em: $this->em,
            client: $client,
            status: 'ACTIVE',
            courtOrderUid: $courtOrderUid,
            type: 'SINGLE',
            deputy: $deputy,
            deputyDischarged: false
        );
        $courtOrder->addReport(report: $report);
        $this->em->persist(entity: $courtOrder);
        
        $results = $this->sut->findReportsInfoByUid(uid: $deputyUid);
        
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
        self::assertEquals($courtOrder->getUid(), $results[0]['courtOrder']['courtOrderUid']);
        self::assertArrayHasKey('report', $results[0]);
        self::assertArrayHasKey('type', $results[0]['report']);
        self::assertEquals($report->getType(), $results[0]['report']['type']);
    }

    public function testFindReportsInfoByUidIsNull()
    {
        $deputyUid = '70000022';
        $results = $this->sut->findReportsInfoByUid(uid: $deputyUid);

        self::assertNull($results);
    }
}
