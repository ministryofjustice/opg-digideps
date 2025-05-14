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

    public function findAllPaged(int $pageSize = 1000): \Traversable
    {
        $query = $this->getEntityManager()->createQuery("SELECT sd FROM App\Entity\StagingDeputyship sd");

        // there will be at least one page, but we'll set this accurately when we've retrieved the first page
        $numPages = 1;

        $currentPage = 1;
        while ($numPages >= $currentPage) {
            $pagedQuery = $query->setFirstResult(($currentPage - 1) * $pageSize)->setMaxResults($pageSize);
            $page = new Paginator($pagedQuery, fetchJoinCollection: false);

            // get the total number of results and reset the number of pages after retrieving the first page of results
            if (1 === $currentPage) {
                $numResults = count($page);
                $numPages = ceil($numResults / $pageSize);
            }

            foreach ($page as $deputyship) {
                yield $deputyship;
            }

            ++$currentPage;
        }
    }
}
