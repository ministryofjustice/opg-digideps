<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\StagingDeputyship;
use App\Entity\StagingSelectedCandidate;
use App\Model\QueryPager;
use App\v2\Registration\Enum\DeputyshipCandidateAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
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
     * @return \Traversable<array<string, mixed>>
     *
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getDistinctOrderedCandidates(): \Traversable
    {
        // order by is required for paged queries, to prevent duplicate rows being returned
        $pageQueryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('ssc')
            ->from(StagingSelectedCandidate::class, 'ssc')
            ->distinct()
            ->orderBy('ssc.id', 'ASC');

        $queryPager = new QueryPager($pageQueryBuilder);

        /** @var \Traversable<array<string, mixed>> $rows */
        $rows = $queryPager->getRows();

        return $rows;
    }

    public function getOrdersWithPossibleReportTypeChange(): \Traversable
    {
       // Actions where a report type could change
        $actionsNeeded = [
            DeputyshipCandidateAction::InsertOrderDeputy,
            DeputyshipCandidateAction::InsertOrderReport,
            DeputyshipCandidateAction::UpdateDeputyStatus
        ];

        $pageQueryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('ssc.orderUid', 'ssc.action', 'ssc.orderType', 'ssc.reportType', 'sd.isHybrid', 'ssc.deputyType')
            ->from(StagingSelectedCandidate::class, 'ssc')
            ->innerJoin(StagingDeputyship::class, 'sd', Join::WITH, 'ssc.orderUid = sd.orderUid')
            ->distinct()
            ->where('ssc.action IN (:actions)')
            ->setParameter('actions', $actionsNeeded)
            ->orderBy('ssc.orderUid', 'ASC');

        $queryPager = new QueryPager($pageQueryBuilder);

        /** @var \Traversable<array<string, mixed>> $rows */
        $rows = $queryPager->getRows();

        return $rows;
    }
}
