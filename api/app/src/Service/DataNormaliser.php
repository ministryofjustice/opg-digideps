<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Service;

class DataNormaliser
{
    public static function normalisePostcode(string $postcode): string
    {
        return mb_strtolower(str_replace(' ', '', $postcode));
    }
}
