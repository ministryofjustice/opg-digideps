<?php declare(strict_types=1);

namespace AppBundle\Service\Time;

class DateTimeProvider
{
    /**
     * @param string|null $dateTime
     * @return \DateTime
     * @throws \Exception
     */
    public function getDateTime(?string $dateTime = null)
    {
        $dateTime = is_null($dateTime) ? 'now' : $dateTime;
        return new \DateTime($dateTime);
    }
}
