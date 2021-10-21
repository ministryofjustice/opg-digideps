<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Report\IncomeReceivedOnClientsBehalf;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class IncomeReceivedOnClientsBehalfRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IncomeReceivedOnClientsBehalf::class);
    }

    public function delete(string $id)
    {
        $entity = $this->_em->find(IncomeReceivedOnClientsBehalf::class, $id);

        $this->_em->remove($entity);
        $this->_em->flush();
    }
}
