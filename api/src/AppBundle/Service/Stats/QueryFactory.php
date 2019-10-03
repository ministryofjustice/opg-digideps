<?php

namespace AppBundle\Service\Stats;

use Doctrine\ORM\EntityManager;
use AppBundle\Service\Stats\Query\Query;

class QueryFactory
{
    /** @var EntityManager */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
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
