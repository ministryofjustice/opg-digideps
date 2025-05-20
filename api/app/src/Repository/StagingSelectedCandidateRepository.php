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
     * Get the candidates from the database table, but ordered by order UID and without duplicatec.
     * Ordering is important as the builder will group the candidates on the fly, using the order UID,
     * so that all entities for a single order UID are created together.
     *
     * @return \Traversable<array<string, string>>
     */
    public function getDistinctOrderedCandidates(): \Traversable
    {
        // get number of results
        /** @var int $numCandidates */
        $numCandidates = $this->getEntityManager()
            ->createQuery('SELECT count(1) FROM App\Entity\StagingSelectedCandidate ssc')
            ->getSingleScalarResult();

        $pageSize = 1000;
        $numPages = ceil($numCandidates / $pageSize);

        $query = $this->getEntityManager()->createQuery(
            'SELECT DISTINCT c.action, c.orderUid, c.deputyUid, c.status, c.orderType, c.reportType, c.orderMadeDate, '.
            'c.deputyType, c.deputyStatusOnOrder, c.orderId, c.clientId, c.reportId, c.deputyId, c.ndrId '.
            'FROM App\Entity\StagingSelectedCandidate c ORDER BY c.orderUid ASC'
        );

        $currentPage = 1;
        while ($numPages >= $currentPage) {
            $pagedQuery = $query->setFirstResult(($currentPage - 1) * $pageSize)->setMaxResults($pageSize);
            $results = $pagedQuery->getArrayResult();

            /** @var array<string, string> $deputyship */
            foreach ($results as $deputyship) {
                yield $deputyship;
            }

            ++$currentPage;
        }
    }
}
