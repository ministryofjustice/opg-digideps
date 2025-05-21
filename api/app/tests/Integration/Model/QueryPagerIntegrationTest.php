<?php

declare(strict_types=1);

namespace app\tests\Integration\Model;

use App\Entity\StagingSelectedCandidate;
use App\Model\QueryPager;
use App\v2\Registration\Enum\DeputyshipCandidateAction;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class QueryPagerIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $container = self::bootKernel()->getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        (new ORMPurger($this->entityManager))->purge();
    }

    public function testGetRows(): void
    {
        $courtOrderUid = '99775533';
        $numRowsExpected = 20;

        for ($i = 0; $i < $numRowsExpected; ++$i) {
            $candidate = new StagingSelectedCandidate(DeputyshipCandidateAction::InsertOrder, $courtOrderUid);
            $this->entityManager->persist($candidate);
        }

        $this->entityManager->flush();

        $countQuery = $this->entityManager->createQuery("SELECT count(1) FROM App\Entity\StagingSelectedCandidate ssc");
        $pageQuery = $this->entityManager->createQuery("SELECT ssc FROM App\Entity\StagingSelectedCandidate ssc");

        $sut = new QueryPager($countQuery, $pageQuery);

        // test getting rows as objects
        $generator = $sut->getRows(pageSize: 3, asArray: false);

        $numRowsActual = 0;

        /** @var StagingSelectedCandidate $candidate */
        foreach ($generator as $candidate) {
            ++$numRowsActual;
            self::assertEquals(DeputyshipCandidateAction::InsertOrder, $candidate->action);
            self::assertEquals($courtOrderUid, $candidate->orderUid);
        }

        self::assertEquals($numRowsExpected, $numRowsActual);

        // test getting rows as arrays
        $generator = $sut->getRows(pageSize: 3, asArray: true);

        $numRowsActual = 0;

        foreach ($generator as $candidate) {
            ++$numRowsActual;
            self::assertEquals(DeputyshipCandidateAction::InsertOrder, $candidate['action']);
            self::assertEquals($courtOrderUid, $candidate['orderUid']);
        }

        self::assertEquals($numRowsExpected, $numRowsActual);
    }
}
