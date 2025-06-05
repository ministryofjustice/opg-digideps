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
        $countQuery = $em->createQuery("SELECT COUNT(1) FROM App\Entity\StagingDeputyship sd");
        $pageQuery = $em->createQuery("SELECT sd FROM App\Entity\StagingDeputyship sd");

        $queryPager = new QueryPager($countQuery, $pageQuery);

        return $queryPager->getRows(asArray: false);
    }
}
