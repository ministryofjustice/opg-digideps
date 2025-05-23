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
    private Fixtures $fixtures;
    private DeputyshipProcessingRawDbAccess $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $container = self::bootKernel()->getContainer();
        $this->entityManager = $container->get('doctrine')->getManager('ingestwriter');

        $this->fixtures = new Fixtures($this->entityManager);

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

        self::assertNotFalse($order, 'order was not found');
        self::assertEquals($uid, $order['court_order_uid']);
        self::assertEquals('pfa', $order['order_type']);
        self::assertEquals('ACTIVE', $order['status']);
    }

    /**
     * @throws Exception
     */
    public function testInsertOrderDeputy(): void
    {
        // insert deputy and court order
        $deputy = $this->fixtures->createDeputy();

        $courtOrderUid = uniqid();
        $courtOrder = $this->fixtures->createCourtOrder($courtOrderUid, 'pfa', 'ACTIVE');

        $this->fixtures->persist($deputy, $courtOrder)->flush();

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

        self::assertNotFalse($result, 'court order deputy association was not found');
        self::assertTrue($result['is_active']);
    }

    public function testInsertOrderReport(): void
    {
        // insert report and court order (client is needed by the report)
        $client = $this->fixtures->createClient();
        $report = $this->fixtures->createReport($client);

        $courtOrderUid = uniqid();
        $courtOrder = $this->fixtures->createCourtOrder($courtOrderUid, 'pfa', 'ACTIVE');

        $this->fixtures->persist($client, $report, $courtOrder)->flush();

        // use SUT to add association
        $courtOrderId = $this->sut->findOrderId($courtOrderUid);

        $this->sut->beginTransaction();
        $success = $this->sut->insertOrderReport($courtOrderId, ['reportId' => $report->getId()]);
        $this->sut->endTransaction();

        self::assertTrue($success);

        // check association exists
        $result = $this->getQueryBuilder()
            ->select('*')
            ->from('court_order_report')
            ->where('report_id = ?')
            ->andWhere('court_order_id = ?')
            ->setParameter(0, $report->getId())
            ->setParameter(1, $courtOrderId)
            ->fetchAssociative();

        self::assertNotFalse($result, 'court order report association was not found');
    }
}
