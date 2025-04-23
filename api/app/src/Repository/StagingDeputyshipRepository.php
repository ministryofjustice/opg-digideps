<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\StagingDeputyship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class StagingDeputyshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StagingDeputyship::class);
    }

    public function getCsvDeputyships()
    {
        return $this->getEntityManager()
            ->createQuery("SELECT sd FROM App\Entity\StagingDeputyship sd ORDER BY sd.orderUid")
            ->getResult();
    }
}
