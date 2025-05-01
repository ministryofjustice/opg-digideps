<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
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
 * @ORM\Entity(repositoryClass="App\Repository\StagingDeputyshipRepository")
 */
class StagingDeputyship
{
    /**
     * @ORM\Id
     *
     * @JMS\Type("integer")
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="deputyship_id_seq", allocationSize=1, initialValue=1)
     */
    public int $id;

    /**
     * @ORM\Column(name="order_uid", type="string", length=30)
     */
    #[Serializer\MapCell(column: 'OrderUid')]
    public string $orderUid;

    /**
     * @ORM\Column(name="deputy_uid", type="string", length=30)
     */
    #[Serializer\MapCell(column: 'DeputyUid')]
    public string $deputyUid;

    /**
     * @ORM\Column(name="order_type", type="string", length=30, nullable=true)
     */
    #[Serializer\MapCell(column: 'OrderType')]
    public ?string $orderType;

    /**
     * @ORM\Column(name="order_sub_type", type="string", length=30, nullable=true)
     */
    #[Serializer\MapCell(column: 'OrderSubType')]
    public ?string $orderSubType;

    /**
     * @ORM\Column(name="order_made_date", type="string", length=30, nullable=true)
     */
    #[Serializer\MapCell(column: 'OrderMadeDate')]
    public ?string $orderMadeDate;

    /**
     * @ORM\Column(name="order_status", type="string", length=30, nullable=true)
     */
    #[Serializer\MapCell(column: 'OrderStatus')]
    public ?string $orderStatus;

    /**
     * @ORM\Column(name="order_updated_date", type="string", length=30, nullable=true)
     */
    #[Serializer\MapCell(column: 'OrderUpdatedDate')]
    public ?string $orderUpdatedDate;

    /**
     * @ORM\Column(name="case_number", type="string", length=30, nullable=true)
     */
    #[Serializer\MapCell(column: 'CaseNumber')]
    public ?string $caseNumber;

    /**
     * @ORM\Column(name="client_uid", type="string", length=30, nullable=true)
     */
    #[Serializer\MapCell(column: 'ClientUid')]
    public ?string $clientUid;

    /**
     * @ORM\Column(name="client_status", type="string", length=30, nullable=true)
     */
    #[Serializer\MapCell(column: 'ClientStatus')]
    public ?string $clientStatus;

    /**
     * @ORM\Column(name="client_status_date", type="string", length=30, nullable=true)
     */
    #[Serializer\MapCell(column: 'ClientStatusDate')]
    public ?string $clientStatusDate;

    /**
     * @ORM\Column(name="deputy_type", type="string", length=30, nullable=true)
     */
    #[Serializer\MapCell(column: 'DeputyType')]
    public ?string $deputyType;

    /**
     * This is true if the deputy is ACTIVE on the order, false otherwise.
     *
     * @ORM\Column(name="deputy_status_on_order", type="string", length=30, nullable=true)
     */
    #[Serializer\MapCell(column: 'DeputyStatusOnOrder')]
    public ?string $deputyStatusOnOrder;

    /**
     * @ORM\Column(name="deputy_status_change_date", type="string", length=30, nullable=true)
     */
    #[Serializer\MapCell(column: 'DeputyStatusChangeDate')]
    public ?string $deputyStatusChangeDateString;

    /**
     * @ORM\Column(name="report_type", type="string", length=30, nullable=true)
     */
    #[Serializer\MapCell(column: 'ReportType')]
    public ?string $reportType;

    /**
     * @ORM\Column(name="is_hybrid", type="string", length=30, nullable=true)
     */
    #[Serializer\MapCell(column: 'IsHybrid')]
    public ?string $isHybrid;

    public function deputyIsActiveOnOrder(): bool
    {
        return 'ACTIVE' === $this->deputyStatusOnOrder;
    }

    /**
     * Check that this StagingDeputyship has the necessary data to create a valid update order status candidate.
     * Note that $orderUid and $deputyUid cannot be null.
     */
    public function validForUpdateOrderStatusCandidate(): bool
    {
        return !is_null($this->orderStatus);
    }

    /**
     * Check that this StagingDeputyship has the necessary data to create a valid update or insert deputy <-> order status candidate.
     * Note that $orderUid and $deputyUid cannot be null.
     */
    public function validForOrderDeputyStatusCandidate(): bool
    {
        return !is_null($this->deputyStatusOnOrder);
    }

    /**
     * Check that this StagingDeputyship has the necessary data to create a valid insert order candidate.
     * Note that $orderUid and $deputyUid cannot be null.
     */
    public function validForInsertOrderCandidate(): bool
    {
        return !is_null($this->orderType) && !is_null($this->orderStatus) && !is_null($this->orderMadeDate);
    }
}
