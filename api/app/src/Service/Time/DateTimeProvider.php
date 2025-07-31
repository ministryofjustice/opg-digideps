<?php

declare(strict_types=1);

namespace App\Service\Time;

class DateTimeProvider
{
    public function getDateTime(?string $dateTime = null): \DateTime
    {
        $dateTime = is_null($dateTime) ? 'now' : $dateTime;

        return new \DateTime($dateTime);
    }

    public function getDateTimeImmutable(?string $dateTime = null): \DateTimeImmutable
    {
        $dateTime = is_null($dateTime) ? 'now' : $dateTime;

        return new \DateTimeImmutable($dateTime);
    }
}
