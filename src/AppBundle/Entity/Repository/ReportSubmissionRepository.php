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
     * @return ArrayCollection|ReportSubmission[]
     */
    public function getReportSubmissions($archived)
    {
        $qb = $this->_em->getRepository(ReportSubmission::class)
            ->createQueryBuilder('rs')
            ->leftJoin('rs.report', 'r')
            ->leftJoin('rs.archivedBy', 'ab')
            ->leftJoin('r.client', 'c')
            ->leftJoin('c.users', 'u')
            ->leftJoin('rs.documents', 'd')
            ->orderBy('rs.id', 'DESC');

        if ($archived) {
            $qb->where('rs.archivedBy is not null' );
        } else {
            $qb->where('rs.archivedBy is null' );
        }

        return $qb->getQuery()->getResult();
    }
}
