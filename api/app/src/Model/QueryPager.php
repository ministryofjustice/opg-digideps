<?php

declare(strict_types=1);

namespace App\Model;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;

/**
 * Create a generator from a pair of queries: the first which gets the size of the result set, and
 * the second which gets the data.
 *
 * Results are returned
 */
class QueryPager
{
    public function __construct(
        // this should return a single row with a single column containing the count of rows in the resultset,
        // amenable to being called with getSingleScalarResult()
        private readonly Query $countQuery,

        // query to get a page from the resultset
        private readonly Query $pageQuery,
    ) {
    }

    /**
     * Return rows from the $pagedQuery as an array of arrays; each element is a row in format [<field> => <value>, ...].
     *
     * Set $asArray = false to get objects back (type depends on the $pageQuery); otherwise, this returns array results
     *
     * @return \Traversable<array<string, mixed>>|\Traversable<object>
     *
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getRows(int $pageSize = 1000, bool $asArray = true): \Traversable
    {
        /** @var int $numRows */
        $numRows = $this->countQuery->getSingleScalarResult();

        $numPages = ceil($numRows / $pageSize);

        $currentPage = 1;
        while ($numPages >= $currentPage) {
            $pagedQuery = $this->pageQuery->setFirstResult(($currentPage - 1) * $pageSize)->setMaxResults($pageSize);

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
