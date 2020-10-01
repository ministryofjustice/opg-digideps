<?php declare(strict_types=1);


namespace AppBundle\v2\Registration\Converter;

use AppBundle\Entity\CasRec;

class ReportTypeConverter
{
    public function convertTypeofRepAndCorrefToReportType(string $typeOfRep, string $corref, string $realm)
    {
        return CasRec::getTypeBasedOnTypeofRepAndCorref($typeOfRep, $corref, $realm);
    }
}
