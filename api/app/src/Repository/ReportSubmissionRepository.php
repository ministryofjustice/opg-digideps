<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Report\Document;
use App\Entity\Report\ReportSubmission;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter;

class ReportSubmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReportSubmission::class);
    }

    /**
     * @param string $status        string new|archived
     * @param string $q             serach string
     * @param string $createdByRole see values in USER::ROLE_*
     * @param int    $offset
     * @param int    $limit
     * @param string $orderBy       default createdOn
     * @param string $order         default ASC
     *
     * @return array [  counts=>[new=>integer, archived=>integer],    records => [array<ReportSubmission>]    ]
     */
    public function findByFiltersWithCounts(
        $status,
        $q,
        $createdByRole,
        $offset,
        $limit,
        $orderBy = 'createdOn',
        $order = 'ASC'
    ) {
        $statusFilters = [
            'new' => 'rs.archived = false AND NOT EXISTS (SELECT 1 FROM App:Report\Document d WHERE d.reportSubmission = rs AND d.synchronisationStatus IS NOT NULL)',
            'pending' => 'rs.archived = false AND EXISTS (SELECT 1 FROM App:Report\Document d WHERE d.reportSubmission = rs AND d.synchronisationStatus IS NOT NULL)',
            'archived' => 'rs.archived = true',
        ];

        // BASE QUERY BUILDER with filters (for both count and results)
        $qb = $this->createQueryBuilder('rs');
        $qb
            ->leftJoin('rs.report', 'r')
            ->leftJoin('rs.ndr', 'ndr')
            ->leftJoin('rs.archivedBy', 'ab')
            ->leftJoin('rs.createdBy', 'cb')
            ->leftJoin('r.client', 'c')
            ->leftJoin('ndr.client', 'nc')
        ;

        // search filter
        if ($q) {
            $qb->andWhere(implode(' OR ', [
                // user
                'lower(cb.firstname) LIKE :qLike',
                'lower(cb.lastname) LIKE :qLike',
                // client names and case number (exact match)
                'lower(c.firstname) LIKE :qLike',
                'lower(c.lastname) LIKE :qLike',
                // separate clause to check ndrs
                'lower(nc.firstname) LIKE :qLike',
                'lower(nc.lastname) LIKE :qLike',
                // case number
                'LOWER(c.caseNumber) = LOWER(:q)',
                // separate clause to check ndrs
                'LOWER(nc.caseNumber) = LOWER(:q)',
            ]));

            $qb->setParameter('qLike', '%'.strtolower($q).'%');
            $qb->setParameter('q', strtolower($q));
        }

        // role filter
        if ($createdByRole) {
            $qb->andWhere('cb.roleName LIKE :roleNameLikePrefix');
            $qb->setParameter('roleNameLikePrefix', strtoupper($createdByRole).'%');
        }

        // get results (base query + ordered + pagination + status filter)
        $qbSelect = clone $qb;
        $qbSelect->select('rs');
        if (isset($statusFilters[$status])) {
            $qbSelect->andWhere($statusFilters[$status]);
        }
        $qbSelect
            ->orderBy('rs.'.$orderBy, $order)
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        $this->_em->getFilters()->getFilter('softdeleteable')->disableForEntity(User::class); // disable softdelete for createdBy, needed from admin area
        $this->_em->getFilters()->getFilter('softdeleteable')->disableForEntity(Client::class); // disable softdelete for createdBy, needed from admin area
        $records = $qbSelect->getQuery()->getResult(); /* @var $records ReportSubmission[] */
        $this->_em->getFilters()->enable('softdeleteable');

        // run counts on the base query for each status (new/archived)
        $counts = [];
        foreach ($statusFilters as $k => $v) {
            $qbCount = clone $qb;
            $queryCount = $qbCount->select('count(DISTINCT rs.id)')->andWhere($v)->getQuery();
            $counts[$k] = $queryCount->getSingleScalarResult();
        }

        return [
            'records' => $records,
            'counts' => $counts,
        ];
    }

    /**
     * @param int $limit
     *
     * @return ReportSubmission[]
     */
    public function findDownloadableOlderThan(\DateTime $olderThan, $limit)
    {
        $qb = $this->createQueryBuilder('rs');
        $qb
            ->leftJoin('rs.report', 'r')
            ->where('rs.createdOn <= :olderThan')
            ->andWhere('rs.downloadable = true')
            ->setParameter(':olderThan', $olderThan);

        $qb->setMaxResults($limit);

        return $qb->getQuery()->getResult(); /* @var $records ReportSubmission[] */
    }

    /**
     * @param string $orderBy default createdOn
     * @param string $order   default ASC
     *
     * @return array
     */
    public function findAllReportSubmissions(
        ?\DateTime $fromDate = null,
        ?\DateTime $toDate = null,
        $orderBy = 'createdOn',
        $order = 'ASC'
    ) {
        /** @var SoftDeleteableFilter $filter */
        $filter = $this->_em->getFilters()->getFilter('softdeleteable');
        $filter->disableForEntity(Client::class);

        $qb = $this->createQueryBuilder('rs');
        $qb
            ->leftJoin('rs.createdBy', 'cb')
            ->leftJoin('rs.report', 'r')
            ->leftJoin('r.client', 'c')
            ->leftJoin('rs.ndr', 'ndr')
            ->leftJoin('ndr.client', 'ndrClient')
            ->leftJoin('rs.documents', 'documents');

        $qbSelect = clone $qb;
        $qbSelect
            ->select('rs,r,ndr,cb,c,ndrClient')
            ->andWhere('rs.createdOn >= :fromDate')
            ->andWhere('rs.createdOn <= :toDate')
            ->andWhere('rs.createdOn >= r.submitDate OR rs.createdOn >= ndr.submitDate')
            ->andWhere('r.submitted = true OR ndr.submitted = true')
            ->andWhere('r.submitDate IS NOT NULL OR ndr.submitDate IS NOT NULL')
            ->setParameter(':fromDate', $this->determineCreatedFromDate($fromDate))
            ->setParameter(':toDate', $this->determineCreatedToDate($toDate))
            ->orderBy('rs.'.$orderBy, $order);

        $this->_em->getFilters()->enable('softdeleteable');

        $sql = $qbSelect->getQuery()->getSQL();
        file_put_contents('php://stderr', print_r($sql, true));

        return $qbSelect->getQuery()->getResult();
    }

    /**
     * Calculate FromDate for ReportSubmissions. Used for CSV generation to include weekends reports on Monday.
     *
     * @return \DateTime
     */
    private function determineCreatedFromDate(?\DateTime $date = null)
    {
        $dateFormat = (1 == date('N')) ? 'last Friday midnight' : 'yesterday midnight';

        return ($date instanceof \DateTime) ? $date : new \DateTime($dateFormat);
    }

    /**
     * @return \DateTime
     */
    private function determineCreatedToDate(?\DateTime $date = null)
    {
        return ($date instanceof \DateTime) ? $date : new \DateTime();
    }

    public function findOneByIdUnfiltered($id)
    {
        $this->_em->getFilters()->getFilter('softdeleteable')->disableForEntity(Client::class); // disable softdelete for createdBy, needed from admin area
        $reportSubmission = $this->find($id);
        $this->_em->getFilters()->enable('softdeleteable');

        return $reportSubmission;
    }

    public function updateArchivedStatus(ReportSubmission $reportSubmission): void
    {
        if ($reportSubmission->getDocuments() && !$reportSubmission->getArchived()) {
            $allSynced = true;

            foreach ($reportSubmission->getDocuments() as $document) {
                if (Document::SYNC_STATUS_SUCCESS !== $document->getSynchronisationStatus()) {
                    $allSynced = false;
                }
            }

            if ($allSynced) {
                $reportSubmission->setArchived(true);
                $this->_em->persist($reportSubmission);
                $this->_em->flush();
            }
        }
    }
}
