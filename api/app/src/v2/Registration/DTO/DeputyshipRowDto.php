<?php

declare(strict_types=1);

namespace App\v2\Registration\DTO;

use App\v2\Registration\Enum\ClientStatus;
use App\v2\Registration\Enum\DeputyStatusOnOrder;
use App\v2\Registration\Enum\DeputyType;
use App\v2\Registration\Enum\OrderType;
use App\v2\Registration\Enum\ReportType;
use League\Csv\Serializer;
use League\Csv\Serializer\CastToBool;
use League\Csv\Serializer\CastToDate;

class DeputyshipRowDto
{
    #[Serializer\MapCell(column: 'OrderUid', convertEmptyStringToNull: true)]
    public ?string $orderUid;

    #[Serializer\MapCell(column: 'OrderType', convertEmptyStringToNull: true)]
    public ?OrderType $orderType;

    #[Serializer\MapCell(column: 'OrderSubType', convertEmptyStringToNull: true)]
    public ?string $orderSubType;

    #[Serializer\MapCell(column: 'OrderMadeDate', cast: CastToDate::class, convertEmptyStringToNull: true)]
    public ?\DateTimeInterface $orderMadeDate;

    #[Serializer\MapCell(column: 'OrderStatus', convertEmptyStringToNull: true)]
    public ?string $orderStatus;

    #[Serializer\MapCell(column: 'OrderUpdatedDate', cast: CastToDate::class, convertEmptyStringToNull: true)]
    public ?\DateTimeInterface $orderUpdatedDate;

    #[Serializer\MapCell(column: 'CaseNumber', convertEmptyStringToNull: true)]
    public ?string $caseNumber;

    #[Serializer\MapCell(column: 'ClientUid', convertEmptyStringToNull: true)]
    public ?string $clientUid;

    #[Serializer\MapCell(column: 'ClientStatus', convertEmptyStringToNull: true)]
    public ?ClientStatus $clientStatus;

    #[Serializer\MapCell(column: 'ClientStatusDate', cast: CastToDate::class, convertEmptyStringToNull: true)]
    public ?\DateTimeInterface $clientStatusDate;

    #[Serializer\MapCell(column: 'DeputyUid', convertEmptyStringToNull: true)]
    public ?string $deputyUid;

    #[Serializer\MapCell(column: 'DeputyType', convertEmptyStringToNull: true)]
    public ?DeputyType $deputyType;

    #[Serializer\MapCell(column: 'DeputyStatusOnOrder', convertEmptyStringToNull: true)]
    public ?DeputyStatusOnOrder $deputyStatusOnOrder;

    #[Serializer\MapCell(column: 'DeputyStatusChangeDate', convertEmptyStringToNull: true)]
    public ?string $deputyStatusChangeDateString;

    #[Serializer\MapCell(column: 'ReportType', convertEmptyStringToNull: true)]
    public ?ReportType $reportType;

    #[Serializer\MapCell(column: 'IsHybrid', cast: CastToBool::class, convertEmptyStringToNull: false)]
    public bool $isHybrid;
}
