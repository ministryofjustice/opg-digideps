<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\StagingSelectedCandidate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StagingSelectedCandidate>
 */
class StagingSelectedCandidateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StagingSelectedCandidate::class);
    }

    /**
     * Get the candidates from the database table, but ordered by order UID and without duplicates.
     * Ordering is important as the builder will group the candidates on the fly, using the order UID,
     * so that all entities for a single order UID are created together.
     *
     * @return iterable<StagingSelectedCandidate>
     */
    public function getDistinctOrderedCandidates(): iterable
    {
        /** @var iterable<StagingSelectedCandidate> $result */
        $result = $this->createQueryBuilder('sc')
            ->select()
            ->distinct()
            ->orderBy('sc.orderUid', 'ASC')
            ->getQuery()
            ->getResult();

        return $result;
    }
}
