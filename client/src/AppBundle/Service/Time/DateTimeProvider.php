<?php declare(strict_types=1);

namespace AppBundle\Service\Time;

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
