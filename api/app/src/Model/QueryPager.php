<?php

declare(strict_types=1);

namespace App\Model;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

/**
 * Create a generator from a pair of queries: the first which gets the size of the result set, and
 * the second which gets the data.
 */
class QueryPager
{
    public function __construct(
        // query builder set up with a query which will get a page from the resultset; this should always include an
        // ORDER BY clause to ensure that results are returned in a consistent order, as we are going to be paging
        // with this query
        private readonly QueryBuilder $pageQueryBuilder,
    ) {
    }

    /**
     * By default, returns rows from the $pagedQuery as an array of arrays; each element is a row in format
     * [<field> => <value>, ...].
     * Set $asArray = false to get objects back instead (type depends on the $pageQuery).
     *
     * @return \Traversable<array<string, mixed>>|\Traversable<object>
     *
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getRows(int $pageSize = 1000, bool $asArray = true, int $limit = 0): \Traversable
    {
        $pageQuery = $this->pageQueryBuilder->getQuery();
        $countQuery = (clone $this->pageQueryBuilder)->resetDQLPart('orderBy')->select('COUNT(1)')->getQuery();

        /** @var int $numRows */
        $numRows = $countQuery->getSingleScalarResult();

        if ($limit > 0 && $numRows > $limit) {
            $numRows = $limit;
        }

        if ($limit > 0 && $pageSize > $limit) {
            $pageSize = $limit;
        }

        $numPages = ceil($numRows / $pageSize);

        $currentPage = 1;
        while ($numPages >= $currentPage) {
            // ensure we don't fetch more records than we intend to
            $currentPageSize = $pageSize;

            if ($limit > 0) {
                $rowsFetchedSoFar = ($currentPage - 1) * $pageSize;
                $rowsRemaining = $limit - $rowsFetchedSoFar;
                $currentPageSize = min($pageSize, $rowsRemaining);
            }

            $pagedQuery = $pageQuery->setFirstResult(($currentPage - 1) * $pageSize)->setMaxResults($currentPageSize);

            if ($asArray) {
                /** @var iterable<array<string, mixed>> $rows */
                $rows = $pagedQuery->getArrayResult();
            } else {
                /** @var iterable<object> $rows */
                $rows = $pagedQuery->getResult();
            }

            foreach ($rows as $row) {
                yield $row;
            }

            ++$currentPage;
        }
    }
}
