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
     * @throws \Exception
     */
    public function findAllReportSubmissionsRawSql(
        ?\DateTime $fromDate = null,
        ?\DateTime $toDate = null
    ): array {
        $now = new \DateTime();
        $fromDateStrFormatted = ($fromDate ?? $now)->format('Ymd').' 000000';
        $toDateStrFormatted = ($toDate ?? $now)->format('Ymd').' 235959';

        $submittedReportsQuery = "
SELECT
    r0_.id AS report_submission_id,
    COALESCE(c3_.case_number, c5_.case_number) AS case_number,
    r0_.created_on AS created_on,
    now() AS scan_date,
    d1_.id AS user_id,
    CASE
        WHEN d6_.is_report_pdf = true AND d6_.filename LIKE '%.pdf'
        THEN d6_.filename
        ELSE NULL
    END AS filename
FROM report_submission r0_
LEFT JOIN dd_user d1_ ON r0_.created_by = d1_.id
LEFT JOIN report r2_ ON r0_.report_id = r2_.id
LEFT JOIN client c3_ ON r2_.client_id = c3_.id
LEFT JOIN odr o4_ ON r0_.ndr_id = o4_.id
LEFT JOIN client c5_ ON o4_.client_id = c5_.id
LEFT JOIN document d6_ ON r0_.id = d6_.report_submission_id
WHERE r0_.created_on >= '$fromDateStrFormatted' AND r0_.created_on <= '$toDateStrFormatted'
  AND (r0_.created_on >= r2_.submit_date OR r0_.created_on >= o4_.submit_date)
  AND (r2_.submitted = true OR o4_.submitted = true)
  AND (r2_.submit_date IS NOT NULL OR o4_.submit_date IS NOT NULL)
ORDER BY d6_.is_report_pdf ASC;";

        $conn = $this->getEntityManager()->getConnection();

        $docStmt = $conn->prepare($submittedReportsQuery);
        $result = $docStmt->executeQuery();

        return array_filter($this->transformReportSubmissionsRawSql($result->fetchAllAssociative()));
    }

    /**
     * @throws \Exception
     */
    private function transformReportSubmissionsRawSql(array $results): array
    {
        $now = new \DateTime();
        $reportSubmissionsDetails = [];
        foreach ($results as $row) {
            $created_on = new \DateTime($row['created_on']);
            $data = [];
            $data['id'] = $row['report_submission_id'];
            $data['case_number'] = $row['case_number'];
            $data['date_received'] = $created_on->format('Y-m-d');
            $data['scan_date'] = $now->format('Y-m-d');
            $data['document_id'] = $row['filename'];
            $data['document_type'] = 'Reports';
            $data['form_type'] = 'Reports General';
            $reportSubmissionsDetails[] = $data;
        }

        return $reportSubmissionsDetails;
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
        string $orderBy = 'createdOn',
        string $order = 'ASC'
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
