<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Repository;

use OPG\Digideps\Backend\Entity\CourtOrderDeputy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CourtOrderDeputy>
 */
class CourtOrderDeputyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CourtOrderDeputy::class);
    }

    public function getDeputyOnCourtOrder(int $courtOrderId, int $deputyId): ?CourtOrderDeputy
    {
        /* @var ?CourtOrderDeputy */
        return $this->findOneBy(['courtOrder' => $courtOrderId, 'deputy' => $deputyId]);
    }
}
