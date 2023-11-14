<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\NamedDeputy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NamedDeputyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NamedDeputy::class);
    }

    public function countAllEntities()
    {
        return $this
            ->getEntityManager()
            ->createQuery('SELECT COUNT(nd.id) FROM App\Entity\NamedDeputy nd')
            ->getSingleScalarResult();
    }
}
