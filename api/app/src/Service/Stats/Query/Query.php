<?php

namespace App\Service\Stats\Query;

use App\Service\Stats\StatsQueryParameters;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

abstract class Query
{
    abstract protected function getAggregation(): string;

    abstract protected function getSupportedDimensions(): array;

    abstract protected function getSubquery(): string;

    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    public function execute(StatsQueryParameters $sq)
    {
        if (is_array($sq->getDimensions())) {
            $this->checkDimensions($sq->getDimensions());
        }

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('amount', 'amount');

        if (is_array($sq->getDimensions())) {
            foreach ($sq->getDimensions() as $dimension) {
                $rsm->addScalarResult($dimension, $dimension);
            }
        }

        $query = $this->em->createNativeQuery($this->constructQuery($sq), $rsm);

        if ($sq->queryHasDateConstraint()) {
            $startDate = (clone $sq->getStartDate())->setTime(0, 0, 0);
            $endDate = (clone $sq->getEndDate())->setTime(23, 59, 59);

            $query->setParameter('startDate', $startDate->format('Y-m-d H:i:s'));
            $query->setParameter('endDate', $endDate->format('Y-m-d H:i:s'));
        }

        return $query->getResult();
    }

    /**
     * Check all requested are supported by the requested metric.
     *
     * @throws \Exception
     */
    protected function checkDimensions(array $dimensions)
    {
        foreach ($dimensions as $index => $dimensionName) {
            if (!in_array($dimensionName, $this->getSupportedDimensions())) {
                throw new \Exception("Metric does not support \"$dimensionName\" dimension");
            }
        }
    }

    /**
     * Build an SQL query.
     *
     * @return string
     */
    protected function constructQuery(StatsQueryParameters $sq)
    {
        $columns = [
            $this->getAggregation().' amount',
        ];

        if (is_array($sq->getDimensions())) {
            foreach ($sq->getDimensions() as $dimension) {
                $columns[] = "t.{$dimension} \"{$dimension}\"";
            }
        }

        $select = implode(', ', $columns);

        $sql = "SELECT $select FROM ({$this->getSubquery()}) t";

        if ($sq->queryHasDateConstraint()) {
            $sql .= ' WHERE t.date >= :startDate AND t.date <= :endDate';
        }

        if (is_array($sq->getDimensions())) {
            $sql .= ' GROUP BY '.implode(', ', $sq->getDimensions());
        }

        return $sql;
    }
}
