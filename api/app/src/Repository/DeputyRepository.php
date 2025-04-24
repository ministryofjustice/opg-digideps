<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Deputy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Deputy>
 */
class DeputyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Deputy::class);
    }

    /**
     * Return a mapping from deputy UID to deputy ID.
     *
     * @return array<string, int>
     */
    public function getUidToIdMapping(): array
    {
        $deputies = $this->findAll();

        $mapping = [];

        foreach ($deputies as $deputy) {
            $mapping[$deputy->getDeputyUid()] = $deputy->getId();
        }

        return $mapping;
    }
}
