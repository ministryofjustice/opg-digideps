<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Ndr\Ndr;
use DateTime;

class NdrHelpers
{
    public static function createNdr(): Ndr
    {
        $client = ClientHelpers::createClient();
        $startDate = new DateTime('now');

        return (new Ndr())
            ->setSubmitted(false)
            ->setClient($client)
            ->setId(1)
            ->setStartDate($startDate);
    }

    public static function createSubmittedNdr(): Ndr
    {
        $submittedDate = new DateTime();

        return (self::createNdr())
            ->setSubmitDate($submittedDate)
            ->setSubmitted(true);
    }
}
