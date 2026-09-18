<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CourtOrder;

use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Repository\CourtOrderRepository;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\Report\ReportReassembler;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CourtOrderRelationshipIngester
{
    private CourtOrderRelationshipChanges $changes;

    public function __construct(private CourtOrderRelationshipReader $relationshipReader, private ReportReassembler $reportReassembler, private EntityManagerInterface $entityManager)
    {
        $this->changes = new CourtOrderRelationshipChanges();
    }

    /**
     * @return \Generator<int,CourtOrderRelationshipResult,void,void>
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

    private function processRelationship(CourtOrderRelationship $relationship, CourtOrderRepository $repository): void
    {
        $current = $repository->find($relationship->courtOrderId);
        if ($current !== null && ($current->getOrderKind() !== $relationship->kind || $current->getSibling()?->getId() !== $relationship->siblingId)) {
            $this->updateCourtOrder($current, $relationship, $repository);
        }
    }

    private function updateCourtOrder(CourtOrder $current, CourtOrderRelationship $relationship, CourtOrderRepository $repository): void
    {
        $oldSiblingId = $current->getSibling()?->getId();
        $oldKind = $current->getOrderKind();
        $current->setSibling($relationship->siblingId === null ? null : $repository->find($relationship->siblingId));
        $current->setOrderKind($relationship->kind);
        $this->entityManager->persist($current);
        $this->entityManager->flush();
        $this->changes->add(new CourtOrderRelationshipChange(
            $current->getId(),
            $current->getOrderKind(),
            $current->getSibling()?->getId(),
            $oldKind,
            $oldSiblingId
        ));
    }

    /**
     * @return \Generator<int,CourtOrderRelationshipResult,void,void>
     */
    private function updateCourtOrders(): \Generator
    {
        /** @var CourtOrderRepository $repository */
        $repository = $this->entityManager->getRepository(CourtOrder::class);

        foreach ($this->groupByClientId($this->relationshipReader->read()) as $relationships) {
            foreach ($relationships as $relationship) {
                $this->processRelationship($relationship, $repository);
            }
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
        foreach ($this->changes->drain() as $courtOrderRelationshipChange) {
            yield $this->reportReassembler->reassembleReport($courtOrderRelationshipChange);
        }
    }

    /**
     * @param \Generator<int,CourtOrderRelationship,void,void> $relationships
     * @return \Generator<int,array<CourtOrderRelationship>,void,void>
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
}
