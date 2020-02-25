<?php declare(strict_types=1);

namespace AppBundle\Service;

use Symfony\Component\Intl\Intl;

class IntlService
{
    public function getCountryNameByCountryCode(string $countryCode)
    {
        return Intl::getRegionBundle()->getCountryName($countryCode);
    }
}
