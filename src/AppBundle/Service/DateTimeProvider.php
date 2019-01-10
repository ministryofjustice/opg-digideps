<?php

namespace AppBundle\Service;

class DateTimeProvider
{
    /**
     * @param null $dateTime
     * @return \DateTime
     */
    public function getDateTime($dateTime = null)
    {
        return new \DateTime($dateTime);
    }
}
