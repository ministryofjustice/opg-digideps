<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\StagingDeputyship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    public function findAllPaged(int $pageSize = 1000): iterable
    {
        $query = $this->getEntityManager()->createQuery("SELECT sd FROM App\Entity\StagingDeputyship sd");
        $pagedQuery = $query->setFirstResult(0)->setMaxResults($pageSize);

        // yield the deputyships from the first page
        $page = new Paginator($pagedQuery, fetchJoinCollection: false);
        foreach ($page as $deputyship) {
            yield $deputyship;
        }

        // yield each of the other deputyships (if there is more than 1 page)
        $numResults = count($page);
        $numPages = ceil($numResults / $pageSize);

        $currentPage = 2;
        while ($numPages >= $currentPage) {
            $pagedQuery = $query->setFirstResult(($currentPage - 1) * $pageSize)->setMaxResults($pageSize);
            $page = new Paginator($pagedQuery, fetchJoinCollection: false);
            foreach ($page as $deputyship) {
                yield $deputyship;
            }

            ++$currentPage;
        }
    }
}
