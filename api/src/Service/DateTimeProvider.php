<?php

namespace App\Service;

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
