<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Cleanup\Model;

enum Problem: int
{
    case Insane = 1;
    case NotContinuous = 2;
    case NoOrders = 3;
    case BeforeOrders = 4;
    case MessyOrders = 5;
    case MultipleActiveOrders = 6;
}
