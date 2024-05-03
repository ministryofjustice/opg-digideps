<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Deputy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NamedDeputyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deputy::class);
    }

    public function countAllEntities()
    {
        return $this
            ->getEntityManager()
            ->createQuery('SELECT COUNT(nd.id) FROM App\Entity\NamedDeputy nd')
            ->getSingleScalarResult();
    }
}
