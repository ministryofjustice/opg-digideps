<?php

namespace AppBundle\Service\Stats;

class StatsQueryParameters
{
    private $metric;
    private $dimensions;
    private $startDate;
    private $endDate;
    private $canBeConstrainedByDates = ['satisfaction', 'reportsSubmitted', 'registeredDeputies'];

    /**
     * @param array $parameters
     * @throws \Exception
     */
    public function __construct(array $parameters)
    {
        $this->metric = $parameters['metric'];
        $this->dimensions = $parameters['dimension'];

        if ($this->metric === null) {
            throw new \InvalidArgumentException('Must specify a metric');
        }

        if (!is_array($this->dimensions) && !is_null($this->dimensions)) {
            throw new \InvalidArgumentException('Invalid dimension');
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
     * @param array $parameters
     * @throws \Exception
     */
    private function applyDateConstraints(array $parameters)
    {
        $this->startDate = $parameters['startDate'];
        $this->endDate = $parameters['endDate'];

        if ($this->startDate === null && $this->endDate === null) {
            $this->endDate = new \DateTime();
            $this->startDate = new \DateTime('-30 days');
        } elseif ($this->startDate === null) {
            $this->endDate = new \DateTime($this->endDate);
            $this->startDate = clone $this->endDate;
            $this->startDate->sub(new \DateInterval('P30D'));
        } elseif ($this->endDate === null) {
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
