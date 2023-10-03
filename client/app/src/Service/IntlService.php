<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Intl\Countries;

class IntlService
{
    public function getCountryNameByCountryCode(?string $countryCode): ?string
    {
        if (null === $countryCode) {
            return 'Country not provided';
        }

        return Countries::getName($countryCode);
    }
}
