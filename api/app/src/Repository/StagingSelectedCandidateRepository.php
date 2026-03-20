<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\StagingSelectedCandidate;
use App\Model\QueryPager;
use App\v2\Registration\Enum\DeputyshipCandidateAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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

        $sql = <<<SQL
        SELECT DISTINCT sc.order_uid, sc.action, d.order_type, d.report_type, d.is_hybrid, d.deputy_type
        FROM selectedcandidates sc
        INNER JOIN deputyship d ON sc.order_uid = d.order_uid
        WHERE action IN (:actionsNeeded)
        ORDER BY sc.order_uid
        SQL;

        $pageQueryBuilder = $this->getEntityManager()
            ->getConnection()
            ->prepare($sql)
            ->setParameter('actionsNeeded', $actionsNeeded);

        $queryPager = new QueryPager($pageQueryBuilder);

        /** @var \Traversable<array<string, mixed>> $rows */
        $rows = $queryPager->getRows();

        return $rows;
    }
}
