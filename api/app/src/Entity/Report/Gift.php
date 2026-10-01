<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Event\PreRemoveEventArgs;
use OPG\Digideps\Backend\Entity\Report\Traits\HasBankAccountTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

#[ORM\Table(name: 'gift')]
#[ORM\Entity, ORM\HasLifecycleCallbacks]
class Gift
{
    use HasBankAccountTrait;

    #[JMS\Groups(['gifts'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'gift_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['gifts'])]
    #[ORM\Column(name: 'explanation', type: 'text', nullable: false)]
    private string $explanation;

    #[JMS\Type('string')]
    #[JMS\Groups(['gifts'])]
    #[ORM\Column(name: 'amount', type: 'decimal', precision: 14, scale: 2, nullable: true)]
    private ?string $amount = null;

    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'gifts')]
    private Report $report;

    public function __construct(Report $report, string $explanation)
    {
        $this->report = $report;
        $this->explanation = $explanation;
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

    public function getExplanation(): string
    {
        return $this->explanation;
    }

    public function setExplanation(string $explanation): static
    {
        $this->explanation = $explanation;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(null|float|int|string $amount): static
    {
        $this->amount = $amount !== null ? (string)$amount : null;

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

    #[ORM\PreRemove]
    public function onPreRemove(PreRemoveEventArgs $_): void
    {
        if ($this->getReport()->getGifts()->count() === 1) {
            $this->getReport()->setGiftsExist(null);
        }
    }
}
