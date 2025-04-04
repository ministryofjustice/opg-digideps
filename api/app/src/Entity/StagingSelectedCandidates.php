<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Holds staging data taken from the data processed from the deputyship CSV file from Sirius.
 * This table stores candidates identified from the staging deputyship table that need to be
 * inserted or updated in the court order and linked tables.
 *
 * Does no validation, has no de-duplication, and has no foreign keys.
 * It has a unique key, but only because Doctrine demands one.
 *
 * Column names in the database still match the column names in the CSV, as we do no
 * transformation/translation when dumping data into this table.
 *
 * @ORM\Table(name="selectedCandidates", schema="staging", indexes={
 *
 *   @ORM\Index(name="deputy_uid_idx", columns={"deputy_uid"}),
 *   @ORM\Index(name="order_uid_idx", columns={"order_uid"})
 * })
 *
 * @ORM\Entity
 */
class StagingSelectedCandidates
{
    /**
     * @ORM\Id
     *
     * @ORM\Column(name="order_uid", type="string", length=30)
     */
    public string $orderUid;

    /**
     * @ORM\Id
     *
     * @ORM\Column(name="deputy_uid", type="string", length=30)
     */
    public string $deputyUid;

    /**
     * @ORM\Column(name="action", type="string", length=10)
     */
    public string $action;

    /**
     * @ORM\Column(name="order_status", type="string", length=30, nullable=true)
     */
    public ?string $status;

    /**
     * @ORM\Column(name="report_type", type="string", length=30, nullable=true)
     */
    public ?string $reportType;

    /**
     * @ORM\Column(name="order_made_date", type="string", length=30, nullable=true)
     */
    public ?string $orderMadeDate;

    /**
     * @ORM\Column(name="order_updated_date", type="string", length=30, nullable=true)
     */
    public ?string $orderUpdatedDate;

    /**
     * @ORM\Column(name="deputy_type", type="string", length=30, nullable=true)
     */
    public ?string $deputyType;

    /**
     * @ORM\Column(name="deputy_status_on_order", type="string", length=30, nullable=true)
     */
    public ?string $deputyStatusOnOrder;

    /**
     * @ORM\Column(name="is_hybrid", type="string", length=30, nullable=true)
     */
    public ?string $isHybrid;

    /**
     * @ORM\Column(name="client_id", type="integer",nullable=true)
     */
    public ?string $clientId;

    /**
     * @ORM\Column(name="report_id", type="integer",nullable=true)
     */
    public ?string $reportId;

    /**
     * @ORM\Column(name="deputy_id", type="integer",nullable=true)
     */
    public ?string $deputyId;
}
