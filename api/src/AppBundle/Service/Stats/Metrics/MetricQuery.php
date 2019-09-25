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
     * @param StatsQueryParameters $sq
     * @return mixed
     * @throws \Exception
     */
    public function execute(StatsQueryParameters $sq)
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('amount', 'amount');

        if (!is_null($sq->dimensions)) {
            foreach ($sq->dimensions as $index => $dimensionName) {
                if (!in_array($dimensionName, $this->supportedDimensions)) {
                    throw new \Exception("Metric does not support \"$dimensionName\" dimension");
                }

                $key = "dimension$index";
                $rsm->addScalarResult($key, $dimensionName);
                $dimensions["t.$dimensionName"] = $key;
                $selectDimensions[] = "t.$dimensionName $key";
                $groupDimensions[] = "t.$dimensionName";
            }
        }

        // Retrieve the data, within the date range and grouped by the dimension
        $subQuery = $this->getSubquery();
        //$aggregation = $this->aggregation;
        if (!is_null($dimensions)) {
            $select = implode(', ', $selectDimensions);
            $group = implode(', ', $groupDimensions);
            $query = $this->em->createNativeQuery("SELECT $select, $this->aggregation amount FROM ($subQuery) t WHERE t.date >= :startDate AND t.date <= :endDate GROUP BY $group", $rsm);
        } else {
            $query = $this->em->createNativeQuery("SELECT 'all' dimension, $this->aggregation amount FROM ($subQuery) t WHERE t.date >= :startDate AND t.date <= :endDate", $rsm);
        }

        $query->setParameter('startDate', $sq->startDate->format('Y-m-d H:i:s'));
        $query->setParameter('endDate', $sq->endDate->format('Y-m-d H:i:s'));

        return $query->getResult();
    }

}
