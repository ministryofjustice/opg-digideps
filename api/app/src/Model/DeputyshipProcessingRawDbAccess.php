<?php

declare(strict_types=1);

namespace App\Model;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * Encapsulates raw SQL access to the database for the purpose of ingesting deputyship data from Sirius.
 *
 * DO NOT use this class for mundane database/entity access: it is a deliberate hack for optimising as our
 * entities are too bloated to use directly when doing large-scale database manipulation (as is required for the
 * deputyships CSV ingest).
 */
class DeputyshipProcessingRawDbAccess
{
    public function __construct(
        private readonly EntityManagerInterface $ingestWriterEm,
    ) {
    }

    public function beginTransaction(): void
    {
        $this->ingestWriterEm->beginTransaction();
    }

    public function rollback(): void
    {
        $this->ingestWriterEm->rollback();
    }

    public function endTransaction(): void
    {
        $this->ingestWriterEm->flush();
        $this->ingestWriterEm->commit();
        $this->ingestWriterEm->clear();
    }

    /**
     * @return ?int This is null if there are multiple or no matching orders
     */
    public function findOrderId(?string $orderUid): ?int
    {
        if (is_null($orderUid)) {
            return null;
        }

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');

        $query = $this->ingestWriterEm->createNativeQuery('SELECT id FROM court_order WHERE court_order_uid = :orderUid', $rsm);
        $query->setParameter('orderUid', $orderUid);

        try {
            /** @var int $result */
            $result = $query->getSingleScalarResult();

            return $result;
        } catch (NoResultException|NonUniqueResultException) {
            return null;
        }
    }

    public function insertOrder(array $insertOrder): bool
    {
        try {
            $orderMadeDate = new \DateTime($insertOrder['orderMadeDate'] ?? '');
        } catch (\Exception) {
            return false;
        }

        try {
            $qb = $this->ingestWriterEm->getConnection()->createQueryBuilder();

            $qb->insert('court_order')
                ->values(
                    [
                        'court_order_uid' => ':courtOrderUid',
                        'order_type' => ':orderType',
                        'status' => ':status',
                        'order_made_date' => ':orderMadeDate',
                        'created_at' => 'now()',
                        'updated_at' => 'now()',
                    ]
                )
                ->setParameter('courtOrderUid', $insertOrder['orderUid'])
                ->setParameter('orderType', $insertOrder['orderType'] ?? '')
                ->setParameter('status', $insertOrder['status'] ?? '')
                ->setParameter('orderMadeDate', $orderMadeDate->format('Y-m-d'))
                ->executeQuery();

            return true;
        } catch (Exception) {
            return false;
        }
    }

    public function insertOrderDeputy(int $courtOrderId, array $candidate): bool
    {
        $deputyId = $candidate['deputyId'];
        $deputyActive = true === $candidate['deputyStatusOnOrder'];

        try {
            $this->ingestWriterEm->getConnection()->createQueryBuilder()
                ->insert('court_order_deputy')
                ->values(
                    [
                        'court_order_id' => $courtOrderId,
                        'deputy_id' => $deputyId,
                        'is_active' => ':deputyActive',
                    ]
                )
                ->setParameter('deputyActive', $deputyActive ? 'true' : 'false')
                ->executeQuery();

            return true;
        } catch (Exception) {
            return false;
        }
    }

    public function insertOrderReport(int $courtOrderId, array $candidate): bool
    {
        $reportId = $candidate['reportId'];

        try {
            $this->ingestWriterEm->getConnection()->createQueryBuilder()
                ->insert('court_order_report')
                ->values(
                    [
                        'court_order_id' => $courtOrderId,
                        'report_id' => $reportId,
                    ]
                )
                ->executeQuery();

            return true;
        } catch (Exception) {
            return false;
        }
    }

    public function insertOrderNdr(int $courtOrderId, array $candidate): bool
    {
        $ndrId = $candidate['ndrId'];

        try {
            $this->ingestWriterEm->getConnection()->createQueryBuilder()
                ->update('court_order')
                ->set('ndr_id', $ndrId)
                ->where('id = :id')
                ->setParameter('id', $courtOrderId)
                ->executeQuery();

            return true;
        } catch (Exception) {
            return false;
        }
    }

    public function updateOrderStatus(int $courtOrderId, array $candidate): bool
    {
        $courtOrderStatus = $candidate['status'];

        try {
            $this->ingestWriterEm->getConnection()->createQueryBuilder()
                ->update('court_order')
                ->set('status', $courtOrderStatus)
                ->where('id = :id')
                ->setParameter('id', $courtOrderId)
                ->executeQuery();

            return true;
        } catch (Exception) {
            return false;
        }
    }

    public function updateDeputyStatus(int $courtOrderId, array $candidate): bool
    {
        $isActive = true === $candidate['deputyStatusOnOrder'];
        $deputyId = $candidate['deputyId'];

        try {
            $this->ingestWriterEm->getConnection()->createQueryBuilder()
                ->update('court_order_deputy')
                ->set('is_active', $isActive ? 'true' : 'false')
                ->where('court_order_id = :courtOrderId')
                ->andWhere('deputy_id = :deputyId')
                ->setParameter('courtOrderId', $courtOrderId)
                ->setParameter('deputyId', $deputyId)
                ->executeQuery();

            return true;
        } catch (Exception) {
            return false;
        }
    }
}
