<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\StagingDeputyship;
use App\Model\QueryPager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

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
