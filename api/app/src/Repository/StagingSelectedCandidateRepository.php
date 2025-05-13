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
     * @return StagingSelectedCandidate[]
     */
    public function getDistinctCandidates(): array
    {
        // TODO this only returns up to 100 candidates at the moment, for memory reasons; should return a Generator instead
        /** @var StagingSelectedCandidate[] $result */
        $result = $this->createQueryBuilder('sc')->select()->distinct()->setMaxResults(100)->getQuery()->getResult();

        return $result;
    }
}
