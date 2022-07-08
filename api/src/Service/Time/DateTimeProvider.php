<?php

declare(strict_types=1);

namespace App\Service\Time;

class DateTimeProvider
{
    /**
     * @return \DateTime
     *
     * @throws \Exception
     */
    public function getDateTime(?string $dateTime = null)
    {
        $dateTime = is_null($dateTime) ? 'now' : $dateTime;

        return new \DateTime($dateTime);
    }
}
