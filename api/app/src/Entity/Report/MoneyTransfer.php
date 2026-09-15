<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

#[ORM\Table(name: 'money_transfer')]
#[ORM\Entity, ORM\HasLifecycleCallbacks]
class MoneyTransfer
{
    #[JMS\Groups(['money-transfer'])]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[JMS\Groups(['money-transfer'])]
    #[ORM\Column(name: 'amount', type: 'decimal', precision: 14, scale: 2, nullable: true)]
    private ?string $amount = null;

    #[JMS\Groups(['account'])]
    #[JMS\SerializedName('accountFrom')]
    #[ORM\JoinColumn(name: 'from_account_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: BankAccount::class)]
    private ?BankAccount $from = null;

    #[JMS\Groups(['account'])]
    #[JMS\SerializedName('accountTo')]
    #[ORM\JoinColumn(name: 'to_account_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: BankAccount::class)]
    private ?BankAccount $to = null;

    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'moneyTransfers')]
    private Report $report;

    #[JMS\Groups(['money-transfer'])]
    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    public function __construct(Report $report)
    {
        $this->report = $report;
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

    public function setAmount(null|string|float|int $amount): static
    {
        $this->amount = $amount === null ? null : (string)$amount;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function getFrom(): ?BankAccount
    {
        return $this->from;
    }

    public function getTo(): ?BankAccount
    {
        return $this->to;
    }

    public function setFrom(?BankAccount $from): static
    {
        $this->from = $from;

        return $this;
    }

    public function setTo(?BankAccount $to): static
    {
        $this->to = $to;

        return $this;
    }

    public function getReport(): Report
    {
        return $this->report;
    }

    #[JMS\VirtualProperty]
    #[JMS\Groups(['money-transfer'])]
    #[JMS\Type('integer')]
    #[JMS\SerializedName('reportId')]
    public function getReportId(): int
    {
        return $this->getReport()->getId();
    }

    public function setReport(Report $report): static
    {
        $this->report = $report;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    #[ORM\PreRemove]
    public function onPreRemove(PreRemoveEventArgs $_): void
    {
        if ($this->getReport()->getMoneyTransfers()->count() === 1) {
            $this->getReport()->setNoTransfersToAdd(null);
        }
    }
}
