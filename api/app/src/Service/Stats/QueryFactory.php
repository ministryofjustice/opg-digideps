<?php

namespace App\Service\Stats;

use App\Service\Stats\Query\Query;
use Doctrine\ORM\EntityManagerInterface;

class QueryFactory
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
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
