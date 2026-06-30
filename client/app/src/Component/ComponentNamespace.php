<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component;

enum ComponentNamespace: string
{
    case GLOBAL = '';
    case OPG = 'OPG';
    case GOV = 'GOV';
    case MOJ = 'MOJ';
}
