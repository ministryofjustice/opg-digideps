<?php

namespace App\Service\Stats;

use App\Service\Stats\Query\Query;
use Doctrine\ORM\EntityManagerInterface;

class QueryFactory
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function create(StatsQueryParameters $sq): Query
    {
        $className = 'App\\Service\\Stats\\Query\\'.ucfirst($sq->getMetric()).'Query';

        if (!class_exists($className)) {
            throw new \InvalidArgumentException('Invalid metric given');
        }

        return new $className($this->em);
    }
}
