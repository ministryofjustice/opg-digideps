<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

#[ORM\Table(name: 'debt')]
#[ORM\Entity]
class Debt
{
    /**
     * Hold debts type
     * 1st value = id, 2nd value = hasMoreInformation.
     *
     * @var array<array{string, bool}>
     */
    public static array $debtTypeIds = [
        ['care-fees', false],
        ['credit-cards', false],
        ['loans', false],
        ['other', true],
    ];

    #[JMS\Groups(['debt'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'debt_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'debts')]
    private Report $report;

    /**
     * @see self:$debtTypeIds
     */
    #[JMS\Groups(['debt'])]
    #[ORM\Column(name: 'debt_type_id', type: 'string', nullable: false)]
    private string $debtTypeId;

    #[JMS\Type('string')]
    #[JMS\Groups(['debt'])]
    #[ORM\Column(name: 'amount', type: 'decimal', precision: 14, scale: 2, nullable: true)]
    private ?string $amount;

    #[JMS\Groups(['debt'])]
    #[JMS\Type('boolean')]
    #[ORM\Column(name: 'has_more_details', type: 'boolean', nullable: false)]
    private bool $hasMoreDetails;

    #[JMS\Groups(['debt'])]
    #[ORM\Column(name: 'more_details', type: 'text', nullable: true)]
    private ?string $moreDetails = null;

    public function __construct(Report $report, string $debtTypeId, bool $hasMoreDetails, null|string|int|float $amount = null)
    {
        $this->report = $report;
        $report->addDebt($this);

        $this->debtTypeId = $debtTypeId;
        $this->hasMoreDetails = $hasMoreDetails;
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

    public function getDebtTypeId(): string
    {
        return $this->debtTypeId;
    }

    public function setDebtTypeId(string $debtTypeId): static
    {
        $this->debtTypeId = $debtTypeId;
        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(null|string|int|float $amount): static
    {
        $this->amount = $amount === null ? null : (string)$amount;
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

    public function getHasMoreDetails(): bool
    {
        return $this->hasMoreDetails;
    }

    public function setHasMoreDetails(bool $hasMoreDetails): static
    {
        $this->hasMoreDetails = $hasMoreDetails;
        return $this;
    }

    public function setAmountAndDetails(null|string|int|float $amount, ?string $details): static
    {
        $this->setAmount($amount);

        // reset details if amount is not given, or if more details are not expected
        if (empty($this->getAmount()) || !$this->getHasMoreDetails()) {
            $details = null;
        }

        $this->setMoreDetails($details);

        return $this;
    }
}
