<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use OPG\Digideps\Backend\Entity\Staging\StagingDeputyship;
use OPG\Digideps\Backend\Utility\Query\QueryPager;

/**
 * @extends ServiceEntityRepository<StagingDeputyship>
 */
class StagingDeputyshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StagingDeputyship::class);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function findAllPaged(): \Traversable
    {
        $em = $this->getEntityManager();
        $pageQueryBuilder = $em->createQueryBuilder()
            ->select('sd')
            ->from(StagingDeputyship::class, 'sd')
            ->orderBy('sd.id', 'ASC');

        $queryPager = new QueryPager($pageQueryBuilder);

        return $queryPager->getRows(asArray: false);
    }
}
