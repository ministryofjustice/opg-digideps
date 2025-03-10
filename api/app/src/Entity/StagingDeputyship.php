<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use League\Csv\Serializer;

/**
 * Holds staging data for processing deputyship CSV file from Sirius.
 * Does no validation, has no de-duplication, and has no foreign keys.
 * It has a unique key, but only because Doctrine demands one.
 *
 * Column names in the database (as defined here) *must* match the column names in the CSV, as we do no
 * transformation/translation when dumping data into this table.
 *
 * @ORM\Table(name="deputyship", schema="staging")
 *
 * @ORM\Entity
 *
 * , indexes={
 * ORM\Index(name="deputy_uid_idx", columns={"DeputyUid"}),
 * ORM\Index(name="case_number_idx", columns={"CaseNumber"}),
 * ORM\Index(name="order_uid_idx", columns={"OrderUid"})
 *  }
 */
class StagingDeputyship
{
    /**
     * @ORM\Id
     *
     * @ORM\Column(name="OrderUid", type="string", length=30)
     */
    #[Serializer\MapCell(column: 'OrderUid')]
    public ?string $orderUid;

    /**
     * @ORM\Id
     *
     * @ORM\Column(name="DeputyUid", type="string", length=30)
     */
    #[Serializer\MapCell(column: 'DeputyUid')]
    public ?string $deputyUid;

    /**
     * @ORM\Column(name="OrderType", type="string", length=30)
     */
    #[Serializer\MapCell(column: 'OrderType')]
    public ?string $orderType;

    /**
     * @ORM\Column(name="OrderSubType", type="string", length=30)
     */
    #[Serializer\MapCell(column: 'OrderSubType')]
    public ?string $orderSubType;

    /**
     * @ORM\Column(name="OrderMadeDate", type="string", length=30)
     */
    #[Serializer\MapCell(column: 'OrderMadeDate')]
    public ?string $orderMadeDate;

    /**
     * @ORM\Column(name="OrderStatus", type="string", length=30)
     */
    #[Serializer\MapCell(column: 'OrderStatus')]
    public ?string $orderStatus;

    /**
     * @ORM\Column(name="OrderUpdatedDate", type="string", length=30)
     */
    #[Serializer\MapCell(column: 'OrderUpdatedDate')]
    public ?string $orderUpdatedDate;

    /**
     * @ORM\Column(name="CaseNumber", type="string", length=30)
     */
    #[Serializer\MapCell(column: 'CaseNumber')]
    public ?string $caseNumber;

    /**
     * @ORM\Column(name="ClientUid", type="string", length=30)
     */
    #[Serializer\MapCell(column: 'ClientUid')]
    public ?string $clientUid;

    /**
     * @ORM\Column(name="ClientStatus", type="string", length=30)
     */
    #[Serializer\MapCell(column: 'ClientStatus')]
    public ?string $clientStatus;

    /**
     * @ORM\Column(name="ClientStatusDate", type="string", length=30)
     */
    #[Serializer\MapCell(column: 'ClientStatusDate')]
    public ?string $clientStatusDate;

    /**
     * @ORM\Column(name="DeputyType", type="string", length=30)
     */
    #[Serializer\MapCell(column: 'DeputyType')]
    public ?string $deputyType;

    /**
     * @ORM\Column(name="DeputyStatusOnOrder", type="string", length=30)
     */
    #[Serializer\MapCell(column: 'DeputyStatusOnOrder')]
    public ?string $deputyStatusOnOrder;

    /**
     * @ORM\Column(name="DeputyStatusChangeDate", type="string", length=30)
     */
    #[Serializer\MapCell(column: 'DeputyStatusChangeDate')]
    public ?string $deputyStatusChangeDateString;

    /**
     * @ORM\Column(name="ReportType", type="string", length=30)
     */
    #[Serializer\MapCell(column: 'ReportType')]
    public ?string $reportType;

    /**
     * @ORM\Column(name="IsHybrid", type="string", length=30)
     */
    #[Serializer\MapCell(column: 'IsHybrid')]
    public ?string $isHybrid;
}
