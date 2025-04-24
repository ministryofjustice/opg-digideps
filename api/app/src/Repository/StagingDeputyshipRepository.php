<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\StagingDeputyship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StagingDeputyship>
 */
class StagingDeputyshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StagingDeputyship::class);
    }
}
