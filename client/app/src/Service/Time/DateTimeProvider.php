<?php

declare(strict_types=1);

namespace App\Service\Time;

use DateTime;

class DateTimeProvider
{
    public function getDateTime(?string $dateTime = null): DateTime
    {
        $dateTime = is_null($dateTime) ? 'now' : $dateTime;

        return new DateTime($dateTime);
    }
}
