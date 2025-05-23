<?php

namespace App\v2\Registration\Enum;

enum DeputyshipCandidateAction: string
{
    case UpdateOrderStatus = 'U_OS';
    case UpdateDeputyStatus = 'U_DS';
    case InsertOrderDeputy = 'I_OD';
    case InsertOrder = 'I_O';
    case InsertOrderReport = 'I_OR';
    case InsertOrderNdr = 'I_ON';
}
