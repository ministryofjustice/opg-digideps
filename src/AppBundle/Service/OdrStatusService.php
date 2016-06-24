<?php

namespace AppBundle\Service;

use AppBundle\Entity\Odr\Odr;

class OdrStatusService
{
    const STATE_NOT_STARTED = 'not-started';
    const STATE_INCOMPLETE = 'incomplete';
    const STATE_DONE = 'done';

    /** @var Report */
    private $report;

    public function __construct(Odr $report)
    {
        $this->report = $report;
    }

    /**
     * @return array
     */
    public function getRemainingSections()
    {
        return [];
    }

    /** @return bool */
    public function isReadyToSubmit()
    {
        return count($this->getRemainingSections()) === 0;
    }

    /**
     * @return string $status | null
     */
    public function getStatus()
    {
        return 'notFinished';
    }
}
