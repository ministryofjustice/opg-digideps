<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Report\BankAccount;
use App\Entity\Report\Report;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BankAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BankAccount::class);
    }

    public function getSumOfAccounts(?string $deputyType = null, ?\DateTime $after = null): int
    {
        $query = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('SUM(a.closingBalance)')
            ->from('App\Entity\Report\BankAccount', 'a')
            ->leftJoin('a.report', 'r');

        if ($after) {
            $query
                ->andWhere('r.submitDate > :after')
                ->setParameter('after', $after);
        }

        if ($deputyType) {
            $types = match (strtoupper($deputyType)) {
                'LAY' => Report::getAllLayTypes(),
                'PROF' => Report::getAllProfTypes(),
                'PA' => Report::getAllPaTypes(),
                default => [],
            };

            $query
                ->andWhere('r.type IN (:types)')
                ->setParameter('types', $types);
        }

        return intval($query->getQuery()->getSingleScalarResult());
    }
}
