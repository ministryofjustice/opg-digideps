<?php declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Intl\Intl;

class IntlService
{
    /**
     * @param string|null $countryCode
     * @return string|null
     */
    public function getCountryNameByCountryCode(?string $countryCode) : ?string
    {
        return Intl::getRegionBundle()->getCountryName($countryCode);
    }
}
