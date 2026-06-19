<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Repository;

use OPG\Digideps\Backend\Entity\Report\MoneyReceivedOnClientsBehalf;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MoneyReceivedOnClientsBehalfRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoneyReceivedOnClientsBehalf::class);
    }

    public function delete(string $id): void
    {
        $entity = $this->getEntityManager()->find(MoneyReceivedOnClientsBehalf::class, $id);

        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }
}
