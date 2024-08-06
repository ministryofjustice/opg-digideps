<?php

namespace App\Repository;

use App\Entity\Satisfaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SatisfactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Satisfaction::class);
    }

    /**
     * @return array
     */
    public function findAllSatisfactionSubmissions(
        ?\DateTime $fromDate = null,
        ?\DateTime $toDate = null
    ) {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT s.id, s.score, s.comments, s.deputyrole, s.reporttype, s.created
             FROM App:Satisfaction s
             WHERE (s.report IS NOT NULL OR s.ndr IS NOT NULL)
             AND s.created > :fromDate
             AND s.created < :toDate'
        )
            ->setParameters(['fromDate' => $fromDate, 'toDate' => $toDate]);

        return $query->getResult();
    }
}
