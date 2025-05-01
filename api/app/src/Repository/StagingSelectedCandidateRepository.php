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
    public function getDistinctOrderedCandidates(): iterable
    {
        /** @var StagingSelectedCandidate[] $result */
        $result = $this->createQueryBuilder('sc')->select()->distinct()->getQuery()->getResult();

        return $result;
    }
}
