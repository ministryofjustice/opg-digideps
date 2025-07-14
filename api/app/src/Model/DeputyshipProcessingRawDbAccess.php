<?php

declare(strict_types=1);

namespace App\Model;

use App\v2\Registration\Enum\DeputyshipCandidateAction;
use Doctrine\ORM\EntityManagerInterface;
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
     * @return DeputyshipProcessingRawDbAccessResult $result is the ID int value, or null if not found
     */
    public function findOrderId(?string $orderUid): DeputyshipProcessingRawDbAccessResult
    {
        if (is_null($orderUid)) {
            return new DeputyshipProcessingRawDbAccessResult(DeputyshipCandidateAction::FindOrder, false);
        }

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');

        $query = $this->ingestWriterEm->createNativeQuery('SELECT id FROM court_order WHERE court_order_uid = :orderUid', $rsm);
        $query->setParameter('orderUid', $orderUid);

        try {
            /** @var int $result */
            $result = $query->getSingleScalarResult();

            return new DeputyshipProcessingRawDbAccessResult(DeputyshipCandidateAction::FindOrder, true, $result);
        } catch (\Exception $e) {
            $message = sprintf('could not find court order with UID %s; exception was: %s', $orderUid, $e->getMessage());

            return new DeputyshipProcessingRawDbAccessResult(DeputyshipCandidateAction::FindOrder, false, null, $message);
        }
    }

    public function insertOrder(array $insertOrder): DeputyshipProcessingRawDbAccessResult
    {
        try {
            $orderMadeDate = new \DateTime($insertOrder['orderMadeDate'] ?? '');
        } catch (\Exception $e) {
            return new DeputyshipProcessingRawDbAccessResult(DeputyshipCandidateAction::InsertOrder, false, null, $e->getMessage());
        }

        try {
            $qb = $this->ingestWriterEm->getConnection()->createQueryBuilder();

            $qb->insert('client')
                ->values(
                    [
                        'id' => ':clientId',
                    ]
                )
                ->setParameter('clientId', $insertOrder['clientId'] ?? '')
                ->executeQuery();

            $result = $qb->insert('court_order')
                ->values(
                    [
                        'court_order_uid' => ':courtOrderUid',
                        'order_type' => ':orderType',
                        'status' => ':status',
                        'order_made_date' => ':orderMadeDate',
                        'created_at' => 'now()',
                        'updated_at' => 'now()',
                        'client_id' => ':clientId',
                    ]
                )
                ->setParameter('courtOrderUid', $insertOrder['orderUid'])
                ->setParameter('orderType', $insertOrder['orderType'] ?? '')
                ->setParameter('status', $insertOrder['status'] ?? '')
                ->setParameter('orderMadeDate', $orderMadeDate->format('Y-m-d'))
                ->setParameter('clientId', $insertOrder['clientId'] ?? '')
                ->executeQuery();

            return new DeputyshipProcessingRawDbAccessResult(DeputyshipCandidateAction::InsertOrder, true, $result);
        } catch (\Exception $e) {
            $message = sprintf(
                'insert order not applied for court order UID %s; exception was: %s',
                $insertOrder['orderUid'],
                $e->getMessage()
            );

            return new DeputyshipProcessingRawDbAccessResult(DeputyshipCandidateAction::InsertOrder, false, null, $message);
        }
    }

    public function insertOrderDeputy(int $courtOrderId, array $candidate): DeputyshipProcessingRawDbAccessResult
    {
        $deputyId = $candidate['deputyId'];
        $deputyActive = true === $candidate['deputyStatusOnOrder'];

        try {
            $result = $this->ingestWriterEm->getConnection()->createQueryBuilder()
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

            return new DeputyshipProcessingRawDbAccessResult(DeputyshipCandidateAction::InsertOrderDeputy, true, $result);
        } catch (\Exception $e) {
            $message = sprintf(
                'insert order deputy not applied for court order with UID %s (order ID %d, deputy ID %d); exception was: %s',
                $candidate['orderUid'],
                $courtOrderId,
                $deputyId,
                $e->getMessage()
            );

            return new DeputyshipProcessingRawDbAccessResult(DeputyshipCandidateAction::InsertOrderDeputy, false, null, $message);
        }
    }

    public function insertOrderReport(int $courtOrderId, array $candidate): DeputyshipProcessingRawDbAccessResult
    {
        $reportId = $candidate['reportId'];

        try {
            $result = $this->ingestWriterEm->getConnection()->createQueryBuilder()
                ->insert('court_order_report')
                ->values(
                    [
                        'court_order_id' => $courtOrderId,
                        'report_id' => $reportId,
                    ]
                )
                ->executeQuery();

            return new DeputyshipProcessingRawDbAccessResult(DeputyshipCandidateAction::InsertOrderReport, true, $result);
        } catch (\Exception $e) {
            $message = sprintf(
                'insert order report not applied for court order UID %s (order ID %d, report ID %d); exception was %s',
                $candidate['orderUid'],
                $courtOrderId,
                $reportId,
                $e->getMessage()
            );

            return new DeputyshipProcessingRawDbAccessResult(DeputyshipCandidateAction::InsertOrderReport, false, null, $message);
        }
    }

    public function insertOrderNdr(int $courtOrderId, array $candidate): DeputyshipProcessingRawDbAccessResult
    {
        $ndrId = $candidate['ndrId'];

        try {
            $result = $this->ingestWriterEm->getConnection()->createQueryBuilder()
                ->update('court_order')
                ->set('ndr_id', $ndrId)
                ->where('id = :id')
                ->setParameter('id', $courtOrderId)
                ->executeQuery();

            return new DeputyshipProcessingRawDbAccessResult(DeputyshipCandidateAction::InsertOrderNdr, true, $result);
        } catch (\Exception $e) {
            $message = sprintf(
                'insert order ndr not applied for court order UID %s (order ID %d, NDR ID %d); exception was %s',
                $candidate['orderUid'],
                $courtOrderId,
                $ndrId,
                $e->getMessage()
            );

            return new DeputyshipProcessingRawDbAccessResult(DeputyshipCandidateAction::InsertOrderNdr, false, null, $message);
        }
    }

    public function updateOrderStatus(int $courtOrderId, array $candidate): DeputyshipProcessingRawDbAccessResult
    {
        $courtOrderStatus = $candidate['status'];

        try {
            $result = $this->ingestWriterEm->getConnection()->createQueryBuilder()
                ->update('court_order')
                ->set('status', $courtOrderStatus)
                ->where('id = :id')
                ->setParameter('id', $courtOrderId)
                ->executeQuery();

            return new DeputyshipProcessingRawDbAccessResult(DeputyshipCandidateAction::UpdateOrderStatus, true, $result);
        } catch (\Exception $e) {
            $message = sprintf(
                'update order status not applied for court order UID %s (order ID %d); exception was %s',
                $candidate['orderUid'],
                $courtOrderId,
                $e->getMessage()
            );

            return new DeputyshipProcessingRawDbAccessResult(DeputyshipCandidateAction::UpdateOrderStatus, false, null, $message);
        }
    }

    public function updateDeputyStatus(int $courtOrderId, array $candidate): DeputyshipProcessingRawDbAccessResult
    {
        $isActive = true === $candidate['deputyStatusOnOrder'];
        $deputyId = $candidate['deputyId'];

        try {
            $result = $this->ingestWriterEm->getConnection()->createQueryBuilder()
                ->update('court_order_deputy')
                ->set('is_active', $isActive ? 'true' : 'false')
                ->where('court_order_id = :courtOrderId')
                ->andWhere('deputy_id = :deputyId')
                ->setParameter('courtOrderId', $courtOrderId)
                ->setParameter('deputyId', $deputyId)
                ->executeQuery();

            return new DeputyshipProcessingRawDbAccessResult(DeputyshipCandidateAction::UpdateDeputyStatus, true, $result);
        } catch (\Exception $e) {
            $message = sprintf(
                'update deputy status on order not applied for court order UID %s (order ID %d, deputy ID %d); exception was %s',
                $candidate['orderUid'],
                $courtOrderId,
                $deputyId,
                $e->getMessage()
            );

            return new DeputyshipProcessingRawDbAccessResult(DeputyshipCandidateAction::UpdateDeputyStatus, false, null, $message);
        }
    }
}
