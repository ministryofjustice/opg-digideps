<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Report\Traits\ReportProfDeputyCostsEstimateTrait;

#[ORM\Table(name: 'prof_deputy_estimate_cost')]
#[ORM\Entity]
class ProfDeputyEstimateCost
{
    #[JMS\Groups(['prof-deputy-estimate-costs'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'prof_estimate_cost_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'profDeputyEstimateCosts')]
    private Report $report;

    /**
     * @see ReportProfDeputyCostsEstimateTrait::$profDeputyEstimateCostTypeIds
     */
    #[JMS\Groups(['prof-deputy-estimate-costs'])]
    #[ORM\Column(name: 'prof_deputy_estimate_cost_type_id', type: 'string', nullable: false)]
    private string $profDeputyEstimateCostTypeId;

    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-estimate-costs'])]
    #[ORM\Column(name: 'amount', type: 'decimal', precision: 14, scale: 2, nullable: true)]
    private ?string $amount = null;

    #[JMS\Groups(['prof-deputy-estimate-costs'])]
    #[JMS\Type('boolean')]
    #[ORM\Column(name: 'has_more_details', type: 'boolean', nullable: false)]
    private bool $hasMoreDetails;

    #[JMS\Groups(['prof-deputy-estimate-costs'])]
    #[ORM\Column(name: 'more_details', type: 'text', nullable: true)]
    private ?string $moreDetails = null;

    public function __construct(Report $report, string $profDeputyEstimateCostTypeId, bool $hasMoreDetails = false)
    {
        $this->report = $report;
        $this->profDeputyEstimateCostTypeId = $profDeputyEstimateCostTypeId;
        $this->hasMoreDetails = $hasMoreDetails;
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

    public function getProfDeputyEstimateCostTypeId(): string
    {
        return $this->profDeputyEstimateCostTypeId;
    }

    public function setProfDeputyEstimateCostTypeId(string $profDeputyEstimateCostTypeId): static
    {
        $this->profDeputyEstimateCostTypeId = $profDeputyEstimateCostTypeId;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): static
    {
        $this->amount = $amount;

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
}
