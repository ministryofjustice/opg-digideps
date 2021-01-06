<?php

namespace AppBundle\Service\Stats;

use AppBundle\Service\Stats\Query\Query;
use Doctrine\ORM\EntityManagerInterface;

class QueryFactory
{
    /** @var EntityManagerInterface */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param StatsQueryParameters $sq
     * @return Query
     */
    public function create(StatsQueryParameters $sq): Query
    {
        $className = 'AppBundle\\Service\\Stats\\Query\\'. ucfirst($sq->getMetric()) . 'Query';

        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Invalid metric given');
        }

        return new $className($this->em);
    }
}
