<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Report\Traits\ReportProfDeputyCostsTrait;

#[ORM\Table(name: 'prof_deputy_other_cost')]
#[ORM\Entity]
class ProfDeputyOtherCost
{
    #[JMS\Groups(['prof-deputy-other-costs'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'prof_other_cost_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'profDeputyOtherCosts')]
    private Report $report;

    /**
     * @see ReportProfDeputyCostsTrait::$profDeputyOtherCostTypeIds
     */
    #[JMS\Groups(['prof-deputy-other-costs'])]
    #[ORM\Column(name: 'prof_deputy_other_cost_type_id', type: 'string', nullable: false)]
    private string $profDeputyOtherCostTypeId;

    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-other-costs'])]
    #[ORM\Column(name: 'amount', type: 'decimal', precision: 14, scale: 2, nullable: true)]
    private ?string $amount;

    #[JMS\Groups(['prof-deputy-other-costs'])]
    #[JMS\Type('boolean')]
    #[ORM\Column(name: 'has_more_details', type: 'boolean', nullable: false)]
    private bool $hasMoreDetails;

    #[JMS\Groups(['prof-deputy-other-costs'])]
    #[ORM\Column(name: 'more_details', type: 'text', nullable: true)]
    private ?string $moreDetails = null;

    public function __construct(
        Report $report,
        string $profDeputyOtherCostTypeId,
        bool $hasMoreDetails,
        ?string $amount
    ) {
        $this->report = $report;
        $report->addProfDeputyOtherCost($this);

        $this->profDeputyOtherCostTypeId = $profDeputyOtherCostTypeId;
        $this->hasMoreDetails = $hasMoreDetails;
        $this->amount = $amount;
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

    public function setReport(Report $report): void
    {
        $this->report = $report;
    }

    public function getProfDeputyOtherCostTypeId(): string
    {
        return $this->profDeputyOtherCostTypeId;
    }

    public function setProfDeputyOtherCostTypeId(string $profDeputyOtherCostTypeId): static
    {
        $this->profDeputyOtherCostTypeId = $profDeputyOtherCostTypeId;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(null|string|float|int $amount): void
    {
        $this->amount = $amount === null ? null : (string)$amount;
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

    public function setHasMoreDetails(bool $hasMoreDetails): void
    {
        $this->hasMoreDetails = $hasMoreDetails;
    }
}
