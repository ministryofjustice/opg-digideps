<?php

declare(strict_types=1);

namespace App\Repository;


use App\Entity\CourtOrder;
use App\Service\Search\ClientSearchFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CourtOrderRepository extends ServiceEntityRepository
{
    public function __construct(
        private readonly ManagerRegistry $registry, 
        private readonly ClientSearchFilter $filter
    ) {
        parent::__construct($this->registry, CourtOrder::class);
    }
    
    public function findCourtOrderByUid(int $courtOrderUid): ?CourtOrder
    {   
        return $this->findOneBy(['courtOrderUid' => $courtOrderUid]);
    }
}
