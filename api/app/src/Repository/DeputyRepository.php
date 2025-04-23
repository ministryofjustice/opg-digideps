<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Deputy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DeputyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deputy::class);
    }

    public function getKnownDeputies()
    {
        return $this->getEntityManager()
             ->createQuery("SELECT d.id,d.deputyUid FROM App\Entity\Deputy d")
             ->getResult();
    }
}
