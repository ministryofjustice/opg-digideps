<?php

namespace App\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter;

class SatisfactionRepository extends EntityRepository
{
    /**
     * @param $offset
     * @param $limit
     * @param \DateTime $fromDate
     * @param \DateTime $toDate
     * @param string $orderBy default createdAt
     * @param string $order default ASC
     * @return array
     */
    public function findAllSatisfactionSubmissions(
        \DateTime $fromDate = null,
        \DateTime $toDate = null,
        $orderBy = 'createdAt',
        $order = 'ASC'
    ) {
        $entityManager = $this->getEntityManager(EntityDir\Satisfaction::class);
        $query = $entityManager->createQuery(
            'SELECT s.id, s.score, s.comments, s.deputyrole, s.reporttype, s.created
             FROM App:Satisfaction s
             WHERE s.created > :fromDate
             AND s.created < :toDate'
        )
            ->setParameters(['fromDate' => $fromDate, 'toDate' => $toDate]);
        return $query->getResult();
    }
}
