<?php

namespace OPG\Digideps\Backend\Repository;

use OPG\Digideps\Backend\Entity\Report\MoneyTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MoneyTransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoneyTransaction::class);
    }

    /**
     * Get soft-deleted money transaction objects.
     */
    public function retrieveSoftDeleted($reportId)
    {
        $this->_em->getFilters()->getFilter('softdeleteable')->disableForEntity(MoneyTransaction::class);

        $query = $this
            ->getEntityManager()
            ->createQuery('SELECT t FROM OPG\Digideps\Backend\Entity\Report\MoneyTransaction t WHERE t.report = :reportId AND t.deletedAt is not null')
            ->setParameter('reportId', $reportId);

        $moneyTransactionObject = $query->getResult();

        $this->_em->getFilters()->enable('softdeleteable');

        return $moneyTransactionObject;
    }
}
