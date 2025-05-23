<?php

declare(strict_types=1);

namespace app\tests\Integration\Model;

use App\Model\DeputyshipProcessingRawDbAccess;
use App\Tests\Integration\Fixtures;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeputyshipProcessingRawDbAccessIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private DeputyshipProcessingRawDbAccess $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $container = self::bootKernel()->getContainer();
        $this->entityManager = $container->get('doctrine')->getManager('ingestwriter');

        /** @var DeputyshipProcessingRawDbAccess $sut */
        $sut = $container->get(DeputyshipProcessingRawDbAccess::class);
        $this->sut = $sut;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        (new ORMPurger($this->entityManager))->purge();
    }

    private function getQueryBuilder(): QueryBuilder
    {
        return $this->entityManager->getConnection()->createQueryBuilder();
    }

    /**
     * @throws Exception
     */
    public function testInsertOrderAndFindOrderId(): void
    {
        $uid = substr(uniqid(), 0, 10);

        $candidate = [
            'orderUid' => $uid,
            'orderType' => 'pfa',
            'status' => 'ACTIVE',
            'orderMadeDate' => '2025-05-23 10:10:10',
        ];

        // use SUT to insert the order
        $this->sut->beginTransaction();
        $this->sut->insertOrder($candidate);
        $this->sut->endTransaction();

        // use SUT to find the ID of the order just inserted
        $orderId = $this->sut->findOrderId($uid);

        // get the order from the db and check it looks right
        $order = $this->getQueryBuilder()
            ->select('*')
            ->from('court_order')
            ->where('id = ?')
            ->setParameter(0, $orderId)
            ->fetchAssociative();

        self::assertEquals($uid, $order['court_order_uid']);
        self::assertEquals('pfa', $order['order_type']);
        self::assertEquals('ACTIVE', $order['status']);
    }

    /**
     * @throws Exception
     */
    public function testInsertOrderDeputy(): void
    {
        $fixtures = new Fixtures($this->entityManager);

        // insert deputy and court order
        $deputy = $fixtures->createDeputy();

        $courtOrderUid = uniqid();
        $courtOrder = $fixtures->createCourtOrder($courtOrderUid, 'pfa', 'ACTIVE');

        $fixtures->persist($deputy, $courtOrder)->flush();

        // use SUT to add association
        $courtOrderId = $this->sut->findOrderId($courtOrderUid);

        $this->sut->beginTransaction();
        $success = $this->sut->insertOrderDeputy($courtOrderId, ['deputyStatusOnOrder' => true, 'deputyId' => $deputy->getId()]);
        $this->sut->endTransaction();

        self::assertTrue($success);

        // check there's one association matching deputy and court order IDs
        $result = $this->getQueryBuilder()
            ->select('*')
            ->from('court_order_deputy')
            ->where('deputy_id = ?')
            ->andWhere('court_order_id = ?')
            ->setParameter(0, $deputy->getId())
            ->setParameter(1, $courtOrderId)
            ->fetchAssociative();

        self::assertNotFalse($result);
        self::assertTrue($result['is_active']);
    }
}
