<?php

declare(strict_types=1);

namespace App\Entity;

use App\v2\Registration\Enum\DeputyshipCandidateAction;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

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
 * @ORM\Table(name="selectedCandidates", schema="staging")
 *
 * @ORM\Entity
 */
class StagingSelectedCandidate
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
     * @ORM\SequenceGenerator(sequenceName="candidate_id_seq", allocationSize=1, initialValue=1)
     */
    public ?int $id = null;

    /**
     * @ORM\Column(name="order_uid", type="string", length=30)
     */
    public string $orderUid;

    /**
     * @ORM\Column(name="action", enumType="App\v2\Registration\Enum\DeputyshipCandidateAction", length=30)
     */
    public DeputyshipCandidateAction $action;

    /**
     * @ORM\Column(name="deputy_uid", type="string", length=30, nullable=true)
     */
    public ?string $deputyUid = null;

    /**
     * @ORM\Column(name="order_status", type="string", length=30, nullable=true)
     */
    public ?string $status = null;

    /**
     * @ORM\Column(name="order_type", type="string", length=5, nullable=true)
     */
    public ?string $orderType = null;

    /**
     * @ORM\Column(name="report_type", type="string", length=30, nullable=true)
     */
    public ?string $reportType = null;

    /**
     * @ORM\Column(name="order_made_date", type="string", length=30, nullable=true)
     */
    public ?string $orderMadeDate = null;

    /**
     * @ORM\Column(name="deputy_type", type="string", length=30, nullable=true)
     */
    public ?string $deputyType = null;

    /**
     * @ORM\Column(name="deputy_status_on_order", type="boolean", nullable=true)
     */
    public ?bool $deputyStatusOnOrder = null;

    /**
     * @ORM\Column(name="order_id", type="integer",nullable=true)
     */
    public ?int $orderId = null;

    /**
     * @ORM\Column(name="client_id", type="integer",nullable=true)
     */
    public ?int $clientId = null;

    /**
     * @ORM\Column(name="report_id", type="integer",nullable=true)
     */
    public ?int $reportId = null;

    /**
     * @ORM\Column(name="deputy_id", type="integer",nullable=true)
     */
    public ?int $deputyId = null;

    /**
     * @ORM\Column(name="ndr_id", type="integer",nullable=true)
     */
    public ?int $ndrId = null;

    public function __construct(DeputyshipCandidateAction $action, string $orderUid)
    {
        $this->action = $action;
        $this->orderUid = $orderUid;
    }
}
