<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Report\Debt as ReportDebt;
use AppBundle\Entity\Report\Fee as ReportFee;
use AppBundle\Entity\Report\MoneyShortCategory as ReportMoneyShortCategory;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

class ReportSubmissionRepository extends EntityRepository
{
    /**
     * @param boolean $archived
     * @param string $q serach term
     * @return ArrayCollection|ReportSubmission[]
     */
    public function getReportSubmissions($archived, $q)
    {
        $qb = $this->_em->getRepository(ReportSubmission::class)
            ->createQueryBuilder('rs')
            ->leftJoin('rs.report', 'r')
            ->leftJoin('rs.archivedBy', 'ab')
            ->leftJoin('rs.createdBy', 'cb')
            ->leftJoin('r.client', 'c')
            ->leftJoin('rs.documents', 'd')
            ->orderBy('rs.id', 'DESC');


        // if archivedBy is NOT null, then the submission IS archived
        if ($archived) {
            $qb->where('rs.archivedBy is not null' );
        } else {
            $qb->where('rs.archivedBy is null' );
        }


        // search filter
        // similar to reportController::getAll() filter used by PA dashboard
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

        return $qb->getQuery()->getResult();
    }
}
