<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

#[ORM\Table(name: 'prof_deputy_interim_cost')]
#[ORM\Entity]
class ProfDeputyInterimCost
{
    #[JMS\Type('integer')]
    #[JMS\Groups(['prof-deputy-costs-interim'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'prof_deputy_interim_cost_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'profDeputyInterimCosts')]
    private Report $report;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['prof-deputy-costs-interim'])]
    #[ORM\Column(name: 'date', type: 'datetime', nullable: true)]
    private ?\DateTime $date;

    #[JMS\Type('string')]
    #[JMS\Groups(['prof-deputy-costs-interim'])]
    #[ORM\Column(name: 'amount', type: 'decimal', precision: 14, scale: 2, nullable: true)]
    private ?string $amount;

    public function __construct(Report $report, \DateTime $date, ?string $amount)
    {
        $this->report = $report;
        $this->date = $date;
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

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): void
    {
        $this->date = $date;
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
}
