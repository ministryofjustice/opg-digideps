<?php

namespace App\Repository;

use App\Entity\Report\MoneyTransactionShort;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MoneyTransactionShortRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoneyTransactionShort::class);
    }

    /**
     * Get soft-deleted money transaction objects.
     */
    public function retrieveSoftDeleted($reportId): array
    {
        $this->_em->getFilters()->getFilter('softdeleteable')->disableForEntity(MoneyTransactionShort::class);

        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT t FROM App\Entity\Report\MoneyTransactionShort t WHERE t.report = :reportId AND t.deletedAt is not null')
            ->setParameter('reportId', $reportId);

        $moneyTransactionObject = $query->getArrayResult();

        $this->_em->getFilters()->enable('softdeleteable');

        return $moneyTransactionObject;
    }
}
