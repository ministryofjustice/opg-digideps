<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing\CourtOrder;

use App\Entity\CourtOrder;
use App\v2\Registration\DeputyshipProcessing\Report\ReportReassembler;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

final readonly class CourtOrderRelationshipIngester
{
    public function __construct(private CourtOrderRelationshipReader $relationshipReader, private ReportReassembler $reportReassembler, private EntityManager $entityManager)
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
        $this->entityManager->createQuery($this->entityManager->createQueryBuilder()
            ->update(CourtOrder::class)
            ->set('sibling_id', null)
            ->where("status <> 'ACTIVE'")->getDQL())->execute();
    }

    /**
     * @param EntityRepository<CourtOrder> $repository
     */
    public function processRelationship(CourtOrderRelationship $relationship, EntityRepository $repository): ?CourtOrderRelationshipChange
    {
        $current = $repository->find($relationship->courtOrderId);
        if ($current !== null && ($current->getKind() !== $relationship->kind || $current->getSibling()?->getId() !== $relationship->siblingId)) {
            return $this->updateCourtOrder($current, $relationship, $repository);
        }
        return null;
    }

    /**
     * @param EntityRepository<CourtOrder> $repository
     */
    public function updateCourtOrder(CourtOrder $current, CourtOrderRelationship $relationship, EntityRepository $repository): CourtOrderRelationshipChange
    {
        $oldSiblingId = $current->getSibling()?->getId();
        $oldKind = $current->getKind();
        $current->setSibling($relationship->siblingId === null ? null : $repository->find($relationship->siblingId));
        $current->setKind($relationship->kind);
        $this->entityManager->persist($current);
        return new CourtOrderRelationshipChange($current, $oldKind, $oldSiblingId);
    }

    /**
     * @return array<CourtOrderRelationshipChange>
     */
    public function updateCourtOrders(): array
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
    public function updateReports(array $courtOrderRelationshipChanges): array
    {
        $courtOrderRelationshipResults = [];

        foreach ($courtOrderRelationshipChanges as $courtOrderRelationshipChange) {
            $courtOrderRelationshipResults[] = $this->reportReassembler->reassembleReport($courtOrderRelationshipChange);
        }
        $this->entityManager->flush();

        return $courtOrderRelationshipResults;
    }
}
