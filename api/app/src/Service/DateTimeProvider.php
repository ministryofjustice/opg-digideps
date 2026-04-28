<?php

namespace OPG\Digideps\Backend\Service;

class DateTimeProvider
{
    /**
     * @param string $dateTime
     *
     * @return \DateTime
     */
    public function getDateTime($dateTime = 'now')
    {
        return new \DateTime($dateTime);
    }
}
