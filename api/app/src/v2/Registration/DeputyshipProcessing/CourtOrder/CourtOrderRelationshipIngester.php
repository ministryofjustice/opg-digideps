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
     * @return \Generator<never,int,CourtOrderRelationshipResult,void>
     */
    public function execute(): \Generator
    {
        $this->purgeInactiveRelations();
        return $this->updateCourtOrders();
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
        $this->entityManager->flush();
        return new CourtOrderRelationshipChange($current, $oldKind, $oldSiblingId);
    }

    /**
     * @return \Generator<never,int,CourtOrderRelationshipResult,void>
     */
    private function updateCourtOrders(): \Generator
    {
        $repository = $this->entityManager->getRepository(CourtOrder::class);

        foreach ($this->groupByClientId($this->relationshipReader->read()) as $relationships) {
            $changes = [];
            foreach ($relationships as $relationship) {
                $change = $this->processRelationship($relationship, $repository);
                if ($change !== null) {
                    $changes[] = $change;
                }
            }
            $this->entityManager->flush();
            $results = $this->updateReports($changes);
            $this->entityManager->flush();
            $this->entityManager->clear();

            foreach ($results as $result) {
                yield $result;
            }
        }
    }

    /**
     * @param \Generator<never,int,CourtOrderRelationship,void> $relationships
     * @return \Generator<never,int,array<CourtOrderRelationship>,void>
     */
    private function groupByClientId(\Generator $relationships): \Generator
    {
        /**
         * @var array<CourtOrderRelationship> $buffer
         */
        $buffer = [];
        foreach ($relationships as $relationship) {
            if (empty($buffer) || $buffer[count($buffer) - 1]->clientId  === $relationship->clientId) {
                $buffer[] = $relationship;
            } else {
                yield $buffer;
                $buffer = [$relationship];
            }
        }
        yield $buffer;
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
