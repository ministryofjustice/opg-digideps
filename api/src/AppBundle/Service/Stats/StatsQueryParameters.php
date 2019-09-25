<?php

namespace AppBundle\Service\Stats;

use Symfony\Component\HttpFoundation\Request;

class StatsQueryParameters
{
    public $metric;
    public $dimensions;
    public $startDate;
    public $endDate;

    public function __construct(Request $request)
    {
        $this->metric = $request->query->get('metric');
        $this->dimensions = $request->query->get('dimension');
        $this->startDate = $request->query->get('startDate');
        $this->endDate = $request->query->get('endDate');

        if ($this->metric === null) {
            throw new \Exception('Must specify a metric');
        }

        if (!is_array($this->dimensions) && !is_null($this->dimensions)) {
            throw new \Exception('Invalid dimension');
        }

        if ($this->startDate === null) {
            if ($this->endDate === null) {
                $this->endDate = new \DateTime();
                $this->startDate = new \DateTime('-30 days');
            } else {
                $endDate = new \DateTime($this->endDate);
                $this->startDate = clone $endDate;
                $this->startDate->sub(new \DateInterval('P30D'));
            }
        } elseif ($this->endDate === null) {
            $this->startDate = new \DateTime($this->startDate);
            $this->endDate = clone $this->startDate;
            $this->endDate->add(new \DateInterval('P30D'));
        }

        $this->startDate->setTime(0, 0, 0);
        $this->endDate->setTime(23, 59, 59);
    }
}
