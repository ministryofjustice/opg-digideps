<?php

namespace App\Service\Stats;

class StatsQueryParameters
{
    private $metric;
    private $dimensions;
    private $startDate;
    private $endDate;
    private $canBeConstrainedByDates = ['satisfaction', 'reportsSubmitted', 'registeredDeputies', 'respondents'];

    /**
     * @throws \Exception
     */
    public function __construct(array $parameters)
    {
        $this->metric = isset($parameters['metric']) ? $parameters['metric'] : null;
        $this->dimensions = isset($parameters['dimension']) ? $parameters['dimension'] : null;

        if (null === $this->metric) {
            throw new \InvalidArgumentException('Must specify a metric');
        }

        if (!is_array($this->dimensions) && !is_null($this->dimensions)) {
            throw new \InvalidArgumentException('Dimension should be an array');
        }

        if ($this->queryHasDateConstraint()) {
            $this->applyDateConstraints($parameters);
        }
    }

    /**
     * @return bool
     */
    public function queryHasDateConstraint()
    {
        return in_array($this->metric, $this->canBeConstrainedByDates);
    }

    /**
     * @throws \Exception
     */
    private function applyDateConstraints(array $parameters)
    {
        $this->startDate = isset($parameters['startDate']) ? $parameters['startDate'] : null;
        $this->endDate = isset($parameters['endDate']) ? $parameters['endDate'] : null;

        if (null === $this->startDate && null === $this->endDate) {
            $this->endDate = new \DateTime();
            $this->startDate = new \DateTime('-30 days');
        } elseif (null === $this->startDate) {
            $this->endDate = new \DateTime($this->endDate);
            $this->startDate = clone $this->endDate;
            $this->startDate->sub(new \DateInterval('P30D'));
        } elseif (null === $this->endDate) {
            $this->startDate = new \DateTime($this->startDate);
            $this->endDate = clone $this->startDate;
            $this->endDate->add(new \DateInterval('P30D'));
        } else {
            $this->startDate = new \DateTime($this->startDate);
            $this->endDate = new \DateTime($this->endDate);
        }
    }

    public function getMetric()
    {
        return $this->metric;
    }

    public function getDimensions()
    {
        return $this->dimensions;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }
}
