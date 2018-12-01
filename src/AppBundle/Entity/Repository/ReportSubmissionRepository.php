<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class ReportSubmissionRepository extends EntityRepository
{
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
            'new' => 'rs.archivedBy IS NULL',
            'archived' => 'rs.archivedBy IS NOT NULL',
        ];

        // BASE QUERY BUILDER with filters (for both count and results)
        $qb = $this->createQueryBuilder('rs');
        $qb
            ->leftJoin('rs.report', 'r')
            ->leftJoin('rs.ndr', 'ndr')
            ->leftJoin('rs.archivedBy', 'ab')
            ->leftJoin('rs.createdBy', 'cb')
            ->leftJoin('r.client', 'c')
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
                // case number
                'c.caseNumber = :q'
            ]));
            $qb->setParameter('qLike', '%' . strtolower($q) . '%');
            $qb->setParameter('q' , strtolower($q));
        }
        // role filter
        if ($createdByRole) {
            $qb->andWhere('cb.roleName LIKE :roleNameLikePrefix');
            $qb->setParameter('roleNameLikePrefix', strtoupper($createdByRole) . '%');
        }

        // get results (base query + ordered + pagination + status filter)
        $qbSelect = clone $qb;
        $qbSelect->select('rs');
        if (isset($statusFilters[$status])) {
            $qbSelect->andWhere($statusFilters[$status]);
        }
        $qbSelect
            ->orderBy('rs.' . $orderBy, $order)
            ->setFirstResult($offset)
            ->setMaxResults($limit);
        $this->_em->getFilters()->getFilter('softdeleteable')->disableForEntity(User::class); //disable softdelete for createdBy, needed from admin area
        $records = $qbSelect->getQuery()->getResult(); /* @var $records ReportSubmission[] */
        $this->_em->getFilters()->enable('softdeleteable');

        // run counts on the base query for each status (new/archived)
        $counts = [];
        foreach ($statusFilters as $k=>$v) {
            $qbCount = clone $qb;
            $queryCount = $qbCount->select('count(DISTINCT rs.id)')->andWhere($v)->getQuery();
            $counts[$k] = $queryCount->getSingleScalarResult();
        }

        return [
            'records'=>$records,
            'counts'=>$counts,
        ];
    }

    /**
     * @param \DateTime $olderThan
     * @param int       $limit
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
     * @param $offset
     * @param $limit
     * @param array $fromDate
     * @param array $toDate
     * @param string $orderBy default createdOn
     * @param string $order default ASC
     * @return array
     */
    public function findAllReportSubmissions(
        $offset,
        $limit,
        array $fromDate,
        array $toDate,
        $orderBy = 'createdOn',
        $order = 'ASC'
    ) {

        $qb = $this->createQueryBuilder('rs');
        $qb
            ->leftJoin('rs.createdBy', 'cb')
            ->leftJoin('rs.report', 'r')
            ->leftJoin('r.client', 'c')
            ->leftJoin('rs.ndr', 'ndr')
            ->leftJoin('ndr.client', 'ndrClient')
        ;

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
            ->orderBy('rs.' . $orderBy, $order)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qbSelect->getQuery()->getArrayResult();
    }

    /**
     * Calculate FromDate for ReportSubmissions. Used for CSV generation to include weekends reports on Monday.
     *
     * @param array $fromDate
     * @return \DateTime
     */
    private function determineCreatedFromDate(array $fromDate)
    {
        if (empty($fromDate)) {

            // default
            $fromString = 'yesterday midnight';

            if (date('N') == 1) {
                $fromString = 'last Friday midnight';
            }
            $fromDate = new \DateTime($fromString);

            return $fromDate;
        }

        return new \DateTime($fromDate['date']);
    }

    /**
     * @param array $toDate
     *
     * @return \DateTime
     */
    private function determineCreatedToDate(array $toDate)
    {
        return (empty($toDate)) ? new \DateTime() : new \DateTime($toDate['date']);
    }
}
