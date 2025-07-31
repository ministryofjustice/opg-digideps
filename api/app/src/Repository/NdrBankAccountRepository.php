<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Ndr\BankAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NdrBankAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BankAccount::class);
    }

    public function getSumOfAccounts(?\DateTime $after = null, array $excludeByClientId = []): int
    {
        $query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('SUM(a.balanceOnCourtOrderDate)')
            ->from('App\Entity\Ndr\BankAccount', 'a')
            ->leftJoin('a.ndr', 'ndr');

        if ($after) {
            $query
                ->andWhere('ndr.submitDate > :after')
                ->setParameter('after', $after);
        }

        if (!empty($excludeByClientId)) {
            $query
                ->leftJoin('ndr.client', 'c')
                ->andWhere('c.id NOT IN (:clientIds)')
                ->setParameter('clientIds', $excludeByClientId);
        }

        return intval($query->getQuery()->getSingleScalarResult());
    }
}
