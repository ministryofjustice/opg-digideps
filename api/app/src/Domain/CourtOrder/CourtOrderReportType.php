<?php

declare(strict_types=1);

namespace App\Domain\CourtOrder;

enum CourtOrderReportType: string
{
    case OPG102 = 'OPG102';
    case OPG103 = 'OPG103';
    case OPG104 = 'OPG104';
}
