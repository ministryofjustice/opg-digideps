<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Report\Debt as ReportDebt;
use App\Entity\Report\Fee as ReportFee;
use App\Entity\Report\MoneyShortCategory as ReportMoneyShortCategory;
use App\Entity\Report\Report;
use App\Entity\SynchronisableInterface;
use App\Service\Search\ClientSearchFilter;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\ParameterBag;

class ReportRepository extends ServiceEntityRepository
{
    /** @var ClientSearchFilter */
    private $filter;

    const USER_DETERMINANT = 1;
    const ORG_DETERMINANT = 2;

    public function __construct(ManagerRegistry $registry, ClientSearchFilter $filter)
    {
        parent::__construct($registry, Report::class);
        $this->filter = $filter;
    }

    /**
     * add empty Debts to Report.
     * Called from doctrine listener.
     *
     * @return int changed records
     */
    public function addDebtsToReportIfMissing(Report $report)
    {
        $ret = 0;

        // skips if already added
        if (count($report->getDebts()) > 0) {
            return $ret;
        }

        foreach (ReportDebt::$debtTypeIds as $row) {
            $debt = new ReportDebt($report, $row[0], $row[1], null);
            $this->_em->persist($debt);
            ++$ret;
        }

        return $ret;
    }

    /**
     * @return int|null
     *
     * @throws ORMException
     */
    public function addFeesToReportIfMissing(Report $report)
    {
        if (!$report->isPAreport()) {
            return null;
        }

        $ret = 0;

        // skips if already added
        if (count($report->getFees()) > 0) {
            return $ret;
        }

        foreach (ReportFee::$feeTypeIds as $id => $row) {
            $debt = new ReportFee($report, $id, null);
            $this->_em->persist($debt);
            ++$ret;
        }

        return $ret;
    }

    /**
     * Called from doctrine listener.
     *
     * @return int changed records
     */
    public function addMoneyShortCategoriesIfMissing(Report $report)
    {
        $ret = 0;

        if (count($report->getMoneyShortCategories()) > 0) {
            return $ret;
        }

        $cats = ReportMoneyShortCategory::getCategories('in') + ReportMoneyShortCategory::getCategories('out');
        foreach ($cats as $typeId => $options) {
            $debt = new ReportMoneyShortCategory($report, $typeId, false);
            $this->_em->persist($debt);
            ++$ret;
        }

        return $ret;
    }

    /**
     * @param string $role
     *
     * @return mixed
     */
    public function findAllActiveReportsByCaseNumbersAndRole(array $caseNumbers, $role)
    {
        $qb = $this->createQueryBuilder('r');
        $qb->leftJoin('r.client', 'c')
            ->leftJoin('c.users', 'u')
            ->where('(r.submitted = false OR r.submitted is null) AND r.unSubmitDate IS NULL AND c.caseNumber IN (:caseNumbers) AND u.roleName = :roleName')
            ->setParameter('caseNumbers', $caseNumbers, Connection::PARAM_STR_ARRAY)
            ->setParameter('roleName', $role);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param mixed       $orgIdsOrUserId
     * @param int         $determinant
     * @param string      $select
     * @param string|null $status
     *
     * @return array|mixed|null
     *
     * @throws NonUniqueResultException
     */
    public function getAllByDeterminant($orgIdsOrUserId, $determinant, ParameterBag $query, $select, $status)
    {
        $qb = $this->createQueryBuilder('r');

        if (self::USER_DETERMINANT === $determinant) {
            $qb
                ->select(('count' === $select) ? 'COUNT(DISTINCT r)' : 'r,c')
                ->leftJoin('r.client', 'c')
                ->leftJoin('c.users', 'u')->where('u.id = '.$orgIdsOrUserId);
        } else {
            $qb
                ->select(('count' === $select) ? 'COUNT(DISTINCT r)' : 'r,c,o')
                ->leftJoin('r.client', 'c')
                ->leftJoin('c.organisation', 'o')
                ->where('o.isActivated = true AND o.id in ('.implode(',', $orgIdsOrUserId).')');
        }

        $qb
            ->andWhere('c.archivedAt IS NULL')
            ->andWhere('r.submitted = false OR r.submitted is null');

        if ($searchTerm = $query->get('q')) {
            $this->filter->handleSearchTermFilter($searchTerm, $qb, 'c');
        }

        $endOfToday = new DateTime('today midnight');

        if (Report::STATUS_READY_TO_SUBMIT === $status) {
            $qb->andWhere('r.reportStatusCached = :status AND r.endDate < :endOfToday')
                ->setParameter('status', $status)
                ->setParameter('endOfToday', $endOfToday);
        } elseif (Report::STATUS_NOT_FINISHED === $status) {
            $qb->andWhere('r.reportStatusCached = :status OR (r.reportStatusCached = :readyToSubmit AND r.endDate >= :endOfToday)')
                ->setParameter('status', $status)
                ->setParameter('readyToSubmit', Report::STATUS_READY_TO_SUBMIT)
                ->setParameter('endOfToday', $endOfToday);
        } elseif (Report::STATUS_NOT_STARTED === $status) {
            $qb->andWhere('r.reportStatusCached = :status')
                ->setParameter('status', $status);
        }

        if ('count' === $select) {
            return $qb->getQuery()->getSingleScalarResult();
        }

        $qb
            ->setFirstResult($query->get('offset', 0))
            ->setMaxResults($query->get('limit', 15))
            ->addOrderBy('r.endDate', 'ASC')
            ->addOrderBy('c.caseNumber', 'ASC');

        $result = $qb->getQuery()->getArrayResult();

        return 0 === count($result) ? null : $result;
    }

    /**
     * @throws DBALException
     */
    public function getReportsIdsWithQueuedChecklistsAndSetChecklistsToInProgress(int $limit): array
    {
        $dql = <<<DQL
SELECT c.id as checklist_id, r.id as report_id
FROM App\Entity\Report\Report r
JOIN r.checklist c
JOIN r.reportSubmissions rs
WHERE c.synchronisationStatus = ?1
DQL;

        $query = $this
            ->getEntityManager()
            ->createQuery($dql)
            ->setParameter(1, SynchronisableInterface::SYNC_STATUS_QUEUED)
            ->setMaxResults($limit);

        $result = $query->getArrayResult();

        if (count($result)) {
            $conn = $this->getEntityManager()->getConnection();

            $ids = array_map(function ($result) {
                return $result['checklist_id'];
            }, $result);

            $idsString = implode(',', $ids);
            $queryString = "UPDATE checklist SET synchronisation_status = 'IN_PROGRESS' WHERE id IN ($idsString)";
            $query = $conn->prepare($queryString);
            $query->execute();
        }

        return array_column($result, 'report_id');
    }

    public function countAllEntities()
    {
        return $this
            ->getEntityManager()
            ->createQuery('SELECT COUNT(r.id) FROM App\Entity\Report\Report r')
            ->getSingleScalarResult();
    }

    /**
     * @return Report[]
     */
    public function getAllSubmittedReportsWithin12Months(string $deputyType)
    {
        $oneYearAgo = new DateTime('-1 year');

        $types = match ($deputyType) {
            'LAY' => Report::getAllLayTypes(),
            'PROF' => Report::getAllProfTypes(),
            'PA' => Report::getAllPaTypes(),
            default => [],
        };

        $dql = <<<DQL
SELECT r FROM App\Entity\Report\Report r
WHERE r.submitDate > :oneYearAgo
AND r.type IN (:types)
DQL;

        $query = $this
            ->getEntityManager()
            ->createQuery($dql)
            ->setParameter('oneYearAgo', $oneYearAgo)
            ->setParameter('types', $types);

        return $query->getResult();
    }
}
