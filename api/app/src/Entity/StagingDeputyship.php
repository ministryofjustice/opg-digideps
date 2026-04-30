<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use League\Csv\Serializer;
use OPG\Digideps\Backend\Repository\StagingDeputyshipRepository;

/**
 * Holds staging data for processing deputyship CSV file from Sirius.
 * Does no validation, has no de-duplication, and has no foreign keys.
 * It has a unique key, but only because Doctrine demands one.
 *
 * Column names in the database (as defined here) *must* match the column names in the CSV, as we do no
 * transformation/translation when dumping data into this table.
 */
#[ORM\Table(name: 'deputyship', schema: 'staging')]
#[ORM\Entity(repositoryClass: StagingDeputyshipRepository::class)]
class StagingDeputyship
{
    #[JMS\Type('integer')]
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'deputyship_id_seq', allocationSize: 1, initialValue: 1)]
    public ?int $id = null;

    #[Serializer\MapCell(column: 'OrderUid')]
    #[ORM\Column(name: 'order_uid', type: 'string', length: 30)]
    public string $orderUid;

    #[Serializer\MapCell(column: 'DeputyUid')]
    #[ORM\Column(name: 'deputy_uid', type: 'string', length: 30)]
    public string $deputyUid;

    #[Serializer\MapCell(column: 'OrderType')]
    #[ORM\Column(name: 'order_type', type: 'string', length: 30, nullable: true)]
    public ?string $orderType;

    #[Serializer\MapCell(column: 'OrderSubType')]
    #[ORM\Column(name: 'order_sub_type', type: 'string', length: 30, nullable: true)]
    public ?string $orderSubType;

    #[Serializer\MapCell(column: 'OrderMadeDate')]
    #[ORM\Column(name: 'order_made_date', type: 'string', length: 30, nullable: true)]
    public ?string $orderMadeDate;

    #[Serializer\MapCell(column: 'OrderStatus')]
    #[ORM\Column(name: 'order_status', type: 'string', length: 30, nullable: true)]
    public ?string $orderStatus;

    #[Serializer\MapCell(column: 'OrderUpdatedDate')]
    #[ORM\Column(name: 'order_updated_date', type: 'string', length: 30, nullable: true)]
    public ?string $orderUpdatedDate;

    #[Serializer\MapCell(column: 'CaseNumber')]
    #[ORM\Column(name: 'case_number', type: 'string', length: 30, nullable: true)]
    public ?string $caseNumber;

    #[Serializer\MapCell(column: 'ClientUid')]
    #[ORM\Column(name: 'client_uid', type: 'string', length: 30, nullable: true)]
    public ?string $clientUid;

    #[Serializer\MapCell(column: 'ClientStatus')]
    #[ORM\Column(name: 'client_status', type: 'string', length: 30, nullable: true)]
    public ?string $clientStatus;

    #[Serializer\MapCell(column: 'ClientStatusDate')]
    #[ORM\Column(name: 'client_status_date', type: 'string', length: 30, nullable: true)]
    public ?string $clientStatusDate;

    #[Serializer\MapCell(column: 'DeputyType')]
    #[ORM\Column(name: 'deputy_type', type: 'string', length: 30, nullable: true)]
    public ?string $deputyType;

    /**
     * This is true if the deputy is ACTIVE on the order, false otherwise.
     */
    #[Serializer\MapCell(column: 'DeputyStatusOnOrder')]
    #[ORM\Column(name: 'deputy_status_on_order', type: 'string', length: 30, nullable: true)]
    public ?string $deputyStatusOnOrder;

    #[Serializer\MapCell(column: 'DeputyStatusChangeDate')]
    #[ORM\Column(name: 'deputy_status_change_date', type: 'string', length: 30, nullable: true)]
    public ?string $deputyStatusChangeDateString;

    #[Serializer\MapCell(column: 'ReportType')]
    #[ORM\Column(name: 'report_type', type: 'string', length: 30, nullable: true)]
    public ?string $reportType;

    #[Serializer\MapCell(column: 'IsHybrid')]
    #[ORM\Column(name: 'is_hybrid', type: 'string', length: 30, nullable: true)]
    public ?string $isHybrid;

    public function deputyIsActiveOnOrder(): bool
    {
        return 'ACTIVE' === $this->deputyStatusOnOrder;
    }
}
