<?php

namespace AppBundle\Service\Stats;

use Doctrine\ORM\EntityManager;
use AppBundle\Service\Stats\Metrics\MetricQuery;

class MetricQueryFactory
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
     * @return MetricQuery
     */
    public function create(StatsQueryParameters $sq): MetricQuery
    {
        $className = 'AppBundle\Service\Stats\Metrics\Metric'. ucfirst($sq->getMetric()) . 'Query';

        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Invalid metric given');
        }

        return new $className($this->em);
    }
}
