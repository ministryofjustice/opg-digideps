<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CourtOrderDeputy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CourtOrderDeputyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourtOrderDeputy::class);
    }

    public function isDeputyOnCourtOrder($deputyId, $courtOrderId)
    {
        return $this->findBy(['courtOrder' => $courtOrderId, 'deputy' => $deputyId]);

        //       return !is_null($this->findBy(['courtOrder' => $courtOrderId, 'deputy' => $deputyId]));
    }
}
