<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing\CourtOrder;

use App\Entity\CourtOrder;
use App\v2\Registration\DeputyshipProcessing\Report\ReportReassembler;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final readonly class CourtOrderRelationshipIngester
{
    public function __construct(private CourtOrderRelationshipReader $relationshipReader, private ReportReassembler $reportReassembler, private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return array<CourtOrderRelationshipResult>
     */
    public function execute(): array
    {
        $this->purgeInactiveRelations();
        return $this->updateReports($this->updateCourtOrders());
    }

    private function purgeInactiveRelations(): void
    {
        $this->entityManager->flush();
        $this->entityManager->clear();
        $this->entityManager->createQuery($this->entityManager->createQueryBuilder()
            ->update(CourtOrder::class, 'co')
            ->set('co.sibling', 'NULL')
            ->where("co.status <> 'ACTIVE'")->getDQL())->execute();
    }

    /**
     * @param EntityRepository<CourtOrder> $repository
     */
    private function processRelationship(CourtOrderRelationship $relationship, EntityRepository $repository): ?CourtOrderRelationshipChange
    {
        $current = $repository->find($relationship->courtOrderId);
        if ($current !== null && ($current->getOrderKind() !== $relationship->kind || $current->getSibling()?->getId() !== $relationship->siblingId)) {
            return $this->updateCourtOrder($current, $relationship, $repository);
        }
        return null;
    }

    /**
     * @param EntityRepository<CourtOrder> $repository
     */
    private function updateCourtOrder(CourtOrder $current, CourtOrderRelationship $relationship, EntityRepository $repository): CourtOrderRelationshipChange
    {
        $oldSiblingId = $current->getSibling()?->getId();
        $oldKind = $current->getOrderKind();
        $current->setSibling($relationship->siblingId === null ? null : $repository->find($relationship->siblingId));
        $current->setOrderKind($relationship->kind);
        $this->entityManager->persist($current);
        return new CourtOrderRelationshipChange($current, $oldKind, $oldSiblingId);
    }

    /**
     * @return array<CourtOrderRelationshipChange>
     */
    private function updateCourtOrders(): array
    {
        $courtOrderRelationshipChanges = [];

        $repository = $this->entityManager->getRepository(CourtOrder::class);
        foreach ($this->relationshipReader->read() as $relationship) {
            $change = $this->processRelationship($relationship, $repository);
            if ($change !== null) {
                $courtOrderRelationshipChanges[] = $change;
            }
        }
        $this->entityManager->flush();

        return $courtOrderRelationshipChanges;
    }

    /**
     * @param array<CourtOrderRelationshipChange> $courtOrderRelationshipChanges
     * @return array<CourtOrderRelationshipResult>
     */
    private function updateReports(array $courtOrderRelationshipChanges): array
    {
        $courtOrderRelationshipResults = [];

        foreach ($courtOrderRelationshipChanges as $courtOrderRelationshipChange) {
            $courtOrderRelationshipResults[] = $this->reportReassembler->reassembleReport($courtOrderRelationshipChange);
        }
        $this->entityManager->flush();

        return $courtOrderRelationshipResults;
    }
}
