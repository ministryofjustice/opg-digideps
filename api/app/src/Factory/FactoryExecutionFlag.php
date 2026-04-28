<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Factory;

enum FactoryExecutionFlag: string
{
    case Inactive = 'inactive';
    case Active = 'active';
    case DryRunOnly = 'dry-run-only';
}
