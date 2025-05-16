<?php

namespace App\v2\Registration\Enum;

enum DeputyshipCandidateAction: string
{
    case UpdateOrderStatus = 'UPDATE ORDER STATUS';
    case UpdateDeputyStatus = 'UPDATE DEPUTY STATUS ON ORDER';
    case InsertOrderDeputy = 'INSERT ORDER DEPUTY';
    case InsertOrder = 'INSERT ORDER';
    case InsertOrderReport = 'INSERT ORDER REPORT';
    case InsertOrderNdr = 'INSERT ORDER NDR';
}
