<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Report\ReportSubmission;
use Doctrine\ORM\EntityRepository;

class ReportSubmissionRepository extends EntityRepository
{
    /**
     * @param string $status string new|archived
     * @param string $q serach string
     * @param string $createdByRole see values in USER::ROLE_*
     * @param integer $offset
     * @param integer $limit
     *
     * @return array [  counts=>[new=>integer, archived=>integer],    records => [array<ReportSubmission>]    ]
     */
    public function findByFiltersWithCounts($status, $q, $createdByRole, $offset, $limit)
    {
        $qb = $this->createQueryBuilder('rs');
        $qb
            ->leftJoin('rs.report', 'r')
            ->leftJoin('rs.archivedBy', 'ab')
            ->leftJoin('rs.createdBy', 'cb')
            ->leftJoin('r.client', 'c')
            ->leftJoin('rs.documents', 'd')
            ->orderBy('rs.id', 'DESC');

        // only return submission with at least one document
        // it can be removed when https://opgtransform.atlassian.net/browse/DDPB-1473 gets implemented
        $qb
            ->groupBy('rs')
            ->having(
                $qb->expr()->gt(
                    $qb->expr()->count('d'), 0
                )
            );

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
            $qb->setParameter('q', $q);
        }

        // role filter
        if ($createdByRole) {
            $qb->andWhere('cb.roleName = :roleName');
            $qb->setParameter('roleName', $createdByRole);
        }

        // disable soft delete filter, as deleted user stil need to appear as creator of the submission
        $this->_em->getFilters()->disable('softdeleteable');
        $records = $qb->getQuery()->getResult(); /* @var $records ReportSubmission[] */
        $this->_em->getFilters()->enable('softdeleteable');

        // calculate total counts for each filter
        // note: this has to be done before the status filter is applied, to get the counts for each status
        $counts = [
            'new' => 0,
            'archived' => 0,
        ];
        foreach ($records as $record) {
            if ($record->getArchivedBy()) {
                $counts['archived']++;
            } else {
                $counts['new']++;
            }
        }

        // apply filters (status, offset, limit)
        $records = array_filter($records, function ($report) use ($status) {
            return ($status === 'new')
                ? ($report->getArchivedBy() === null)
                : ($report->getArchivedBy() !== null);
        });
        $records = array_slice($records, $offset, $limit);

        // return counts and records
        return [
            'counts'=>$counts,
            'records'=>$records
        ];
    }
}
