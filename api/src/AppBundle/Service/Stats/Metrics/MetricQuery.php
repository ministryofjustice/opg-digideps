<?php

namespace AppBundle\Service\Stats\Metrics;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use AppBundle\Service\Stats\StatsQueryParameters;

abstract class MetricQuery
{
    private $em;

    protected $aggregation = 'COUNT(1)';
    protected $supportedDimensions = [];

    abstract protected function getSubQuery();

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Check all requested are supported by the requested metric
     * @throws \Exception
     */
    protected function checkDimensions($dimensions)
    {
        if (!is_array($dimensions)) return [];

        foreach ($dimensions as $index => $dimensionName) {
            if (!in_array($dimensionName, $this->supportedDimensions)) {
                throw new \Exception("Metric does not support \"$dimensionName\" dimension");
            }
        }
    }

    /**
     * Build an SQL query
     * @param mixed $dimensions The dimensions to group results by
     * @return string
     */
    protected function constructQuery($dimensions)
    {
        $columns = [
            $this->aggregation . ' amount'
        ];

        if (is_array($dimensions)) {
            foreach ($dimensions as $dimension) {
                $columns[] = "t.{$dimension} \"{$dimension}\"";
            }
        }

        $select = implode(', ', $columns);
        $sql = "SELECT $select FROM ({$this->getSubquery()}) t WHERE t.date >= :startDate AND t.date <= :endDate";

        if (is_array($dimensions)) {
            $sql .= " GROUP BY " . implode(', ', $dimensions);
        }

        return $sql;
    }

    /**
     * @param StatsQueryParameters $sq
     * @return array
     */
    public function execute(StatsQueryParameters $sq)
    {
        $dimensions = $this->checkDimensions($sq->dimensions);

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('amount', 'amount');

        if (is_array($sq->dimensions)) {
            foreach ($sq->dimensions as $dimension) {
                $rsm->addScalarResult($dimension, $dimension);
            }
        }

        $sql = $this->constructQuery($sq->dimensions);

        $query = $this->em->createNativeQuery($sql, $rsm);
        $query->setParameter('startDate', $sq->startDate->format('Y-m-d H:i:s'));
        $query->setParameter('endDate', $sq->endDate->format('Y-m-d H:i:s'));

        return $query->getResult();
    }

}
