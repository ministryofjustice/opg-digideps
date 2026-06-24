<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use OPG\Digideps\Backend\Entity\Traits\IsSoftDeleteableEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;

#[ORM\Table(name: 'money_transaction_short')]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['in' => MoneyTransactionShortIn::class, 'out' => MoneyTransactionShortOut::class])]
abstract class MoneyTransactionShort implements MoneyTransactionInterface
{
    use IsSoftDeleteableEntity;

    #[JMS\Groups(['moneyTransactionsShortIn', 'moneyTransactionsShortOut'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'money_transaction_short_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'moneyTransactionsShort')]
    private Report $report;

    #[JMS\Type('string')]
    #[JMS\Groups(['moneyTransactionsShortIn', 'moneyTransactionsShortOut'])]
    #[ORM\Column(name: 'amount', type: 'decimal', precision: 14, scale: 2, nullable: false)]
    private ?string $amount = null;

    #[JMS\Groups(['moneyTransactionsShortIn', 'moneyTransactionsShortOut'])]
    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['moneyTransactionsShortIn', 'moneyTransactionsShortOut'])]
    #[ORM\Column(name: 'date', type: 'date', nullable: true, options: ['default' => null])]
    private ?\DateTime $date = null;

    public static function factory(string $type, Report $report): MoneyTransactionShortOut|MoneyTransactionShortIn
    {
        switch ($type) {
            case 'in':
                return new MoneyTransactionShortIn($report);
            case 'out':
                return new MoneyTransactionShortOut($report);
        }
        throw new \InvalidArgumentException(__METHOD__ . ': type not recognised');
    }

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

    public function getReport(): Report
    {
        return $this->report;
    }

    public function setReport(Report $report): static
    {
        $this->report = $report;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(null|string|float|int $amount): static
    {
        $this->amount = $amount === null ? null : (string)$amount;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }
}
