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
        $qb = $this->createQueryBuilder('rs')
            ->leftJoin('rs.report', 'r')
            ->leftJoin('rs.archivedBy', 'ab')
            ->leftJoin('rs.createdBy', 'cb')
            ->leftJoin('r.client', 'c')
            ->leftJoin('rs.documents', 'd')
            ->orderBy('rs.id', 'DESC');

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

        if ($createdByRole) {
            $qb->andWhere('cb.roleName = :roleName');
            $qb->setParameter('roleName', $createdByRole);
        }

        $this->_em->getFilters()->disable('softdeleteable');
        $records = $qb->getQuery()->getResult(); /* @var $records ReportSubmission[] */
        $this->_em->getFilters()->enable('softdeleteable');

        // calculate total counts, filter based on status, then and apply last limit/offset
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
        $records = array_filter($records, function ($report) use ($status) {
            return ($status === 'new')
                ? ($report->getArchivedBy() === null)
                : ($report->getArchivedBy() !== null);
        });
        $records = array_slice($records, $offset, $limit);

        return [
            'counts'=>$counts,
            'records'=>$records
        ];
    }
}
