<?php

namespace AppBundle\Service\Stats;

use Doctrine\ORM\EntityManager;
use AppBundle\Service\Stats\Metrics\MetricQuery;

class MetricQueryFactory
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function create(StatsQueryParameters $sq): MetricQuery
    {
        $className = 'AppBundle\Service\Stats\Metrics\Metric'. ucfirst($sq->getMetric()) . 'Query';

        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Bad');
        }

        return new $className($this->em);
    }
}
