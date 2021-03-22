<?php declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Intl\Countries;

class IntlService
{
    /**
     * @param string|null $countryCode
     * @return string|null
     */
    public function getCountryNameByCountryCode(?string $countryCode) : ?string
    {
        if ($countryCode === null) {
            return 'Country not provided';
        }

        return Countries::getName($countryCode);
    }
}
