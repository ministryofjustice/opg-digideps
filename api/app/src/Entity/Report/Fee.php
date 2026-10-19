<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

#[ORM\Table(name: 'fee')]
#[ORM\Entity]
class Fee
{
    /**
     * Hold fee type
     * 1st value = id, 2nd value = hasMoreInformation.
     * If the order or any key is added, update the ReportControllerTest, hardcoded on position and number
     *  in order to keep it simple.
     *
     * @var array<string, bool>
     */
    public static array $feeTypeIds = [
        'work-up-to-and-including-cot-made' => false,
        'annual-management-fee' => false,
        'annual-property-management-fee' => false,
        'preparing-and-lodging-annual-report' => false,
        'completition-of-tax-return' => false,
        'travel-costs' => true,
        'specialist-service' => true,
    ];

    #[JMS\Groups(['fee'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'fee_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'fees')]
    private Report $report;

    /**
     * @see self::$feeTypeIds
     */
    #[JMS\Groups(['fee'])]
    #[ORM\Column(name: 'fee_type_id', type: 'string', nullable: false)]
    private string $feeTypeId;

    #[JMS\Type('string')]
    #[JMS\Groups(['fee'])]
    #[ORM\Column(name: 'amount', type: 'decimal', precision: 14, scale: 2, nullable: true)]
    private ?string $amount = null;

    #[JMS\Groups(['fee'])]
    #[ORM\Column(name: 'more_details', type: 'text', nullable: true)]
    private ?string $moreDetails = null;

    public function __construct(Report $report, string $feeTypeId, string|int|float|null $amount = null)
    {
        $this->report = $report;
        $report->addFee($this);

        $this->feeTypeId = $feeTypeId;
        $this->setAmount($amount);
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function setId(int $id): static
    {
        if ($this->id === null) {
            $this->id = $id;
        } elseif ($id === 0) {
            throw new \DomainException('You may not set the id of an entity to zero.');
        } else {
            throw new \LogicException('You may not set the id of an entity more than once.');
        }

        return $this;
    }

    public function getReport(): Report
    {
        return $this->report;
    }

    public function setReport(Report $report): static
    {
        $this->report = $report;
        return $this;
    }

    public function getFeeTypeId(): string
    {
        return $this->feeTypeId;
    }

    public function setFeeTypeId(string $feeTypeId): static
    {
        $this->feeTypeId = $feeTypeId;
        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(null|int|float|string $amount): static
    {
        $this->amount = $amount !== null ? (string)$amount : null;

        return $this;
    }

    public function getMoreDetails(): ?string
    {
        return $this->moreDetails;
    }

    public function setMoreDetails(?string $moreDetails): static
    {
        $this->moreDetails = $moreDetails;
        return $this;
    }

    #[JMS\Type('boolean')]
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('has_more_details')]
    #[JMS\Groups(['fee'])]
    public function getHasMoreDetails(): bool
    {
        return self::$feeTypeIds[$this->getFeeTypeId()];
    }

    public function setAmountAndDetails(null|int|float|string $amount, ?string $details): static
    {
        $this->setAmount($amount);

        // reset details if amount is not given, or if more details are not expected
        if (empty($amount) || !$this->getHasMoreDetails()) {
            $details = null;
        }

        $this->setMoreDetails($details);
        return $this;
    }
}
