<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Traits\CreateUpdateTimestamps;

#[ORM\Table(name: 'account')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class BankAccount
{
    use CreateUpdateTimestamps;

    /**
     * Keep in sync with client.
     * @var array<string, string> $types
     */
    #[JMS\Exclude]
    public static array $types = [
        'current' => 'Current account',
        'savings' => 'Savings account',
        'isa' => 'ISA',
        'postoffice' => 'Post Office account',
        'cfo' => 'Court Funds Office account',
        'other' => 'Other',
        'other_no_sortcode' => 'Other without sort code',
    ];

    /**
     * Keep in sync with client.
     * @var array<string> $typesNotRequiringSortCode
     */
    #[JMS\Exclude]
    private static array $typesNotRequiringSortCode = [
        'postoffice',
        'cfo',
        'other_no_sortcode',
    ];

    /**
     * Keep in sync with client.
     * @var array<string> $typesNotRequiringBankName
     */
    #[JMS\Exclude]
    private static array $typesNotRequiringBankName = [
        'postoffice',
        'cfo',
    ];

    #[JMS\Groups(['account'])]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'account_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[JMS\Groups(['account'])]
    #[ORM\Column(name: 'bank_name', type: 'string', length: 500, nullable: true)]
    private ?string $bank = null;

    #[JMS\Groups(['account'])]
    #[ORM\Column(name: 'account_type', type: 'string', length: 125, nullable: true)]
    private ?string $accountType = null;

    #[JMS\Groups(['account'])]
    #[ORM\Column(name: 'sort_code', type: 'string', length: 6, nullable: true)]
    private ?string $sortCode = null;

    #[JMS\Groups(['account'])]
    #[ORM\Column(name: 'account_number', type: 'string', length: 4, nullable: true)]
    private ?string $accountNumber = null;

    #[JMS\Groups(['account'])]
    #[JMS\Type('string')]
    #[ORM\Column(name: 'opening_balance', type: 'decimal', precision: 14, scale: 2, nullable: true)]
    private ?string $openingBalance = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['account'])]
    #[ORM\Column(name: 'closing_balance', type: 'decimal', precision: 14, scale: 2, nullable: true)]
    private ?string $closingBalance = null;

    #[JMS\Groups(['account'])]
    #[JMS\Type('boolean')]
    #[ORM\Column(name: 'is_closed', type: 'boolean', nullable: true, options: ['default' => false])]
    private ?bool $isClosed;

    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Report::class, inversedBy: 'bankAccounts')]
    private Report $report;

    /**
     * yes|no|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['account'])]
    #[ORM\Column(name: 'is_joint_account', type: 'string', length: 3, nullable: true)]
    private ?string $isJointAccount = null;

    /**
     * @deprecated hold information about previous data migration
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['account'])]
    #[ORM\Column(name: 'meta', type: 'text', nullable: true)]
    private ?string $meta = null;

    public function __construct(Report $report)
    {
        $this->report = $report;
        $this->createdAt = new \DateTime();
        $this->isClosed = false;
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

    public function setBank(?string $bank): static
    {
        $this->bank = $bank;

        return $this;
    }

    public function getBank(): ?string
    {
        return $this->bank;
    }

    public function getAccountType(): ?string
    {
        return $this->accountType;
    }

    #[JMS\VirtualProperty]
    #[JMS\SerializedName('account_type_text')]
    #[JMS\Groups(['account'])]
    public function getAccountTypeText(): ?string
    {
        return self::$types[$this->getAccountType() ?? ''] ?? null;
    }

    public function setAccountType(?string $accountType): static
    {
        $this->accountType = $accountType;

        return $this;
    }

    public function setSortCode(?string $sortCode): static
    {
        $this->sortCode = $sortCode;

        return $this;
    }

    public function getSortCode(): ?string
    {
        return $this->sortCode ?? '';
    }

    public function setAccountNumber(?string $accountNumber): static
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber ?? '';
    }

    public function setOpeningBalance(null|string|int|float $openingBalance): static
    {
        $this->openingBalance = $openingBalance !== null ? (string)$openingBalance : null;

        return $this;
    }


    public function getOpeningBalance(): ?string
    {
        return $this->openingBalance;
    }

    public function setClosingBalance(null|string|int|float $closingBalance): static
    {
        $this->closingBalance = $closingBalance !== null ? (string)$closingBalance : null;

        return $this;
    }

    public function getClosingBalance(): ?string
    {
        return $this->closingBalance;
    }

    public function getIsClosed(): bool
    {
        return $this->isClosed ?? false;
    }

    public function setIsClosed(bool $isClosed): static
    {
        $this->isClosed = $isClosed;

        return $this;
    }

    public function setReport(Report $report): static
    {
        $this->report = $report;

        return $this;
    }

    public function getReport(): Report
    {
        return $this->report;
    }

    public function requiresBankName(): bool
    {
        return !in_array($this->getAccountType(), self::$typesNotRequiringBankName);
    }

    public function requiresSortCode(): bool
    {
        return !in_array($this->getAccountType(), self::$typesNotRequiringSortCode);
    }

    public function getIsJointAccount(): ?string
    {
        return $this->isJointAccount;
    }

    /**
     * yes/no/null
     */
    public function setIsJointAccount(?string $isJointAccount): static
    {
        if (!is_null($isJointAccount)) {
            $this->isJointAccount = trim(strtolower($isJointAccount));
        }

        return $this;
    }

    /**
     * Get bank account name in one line
     * <bank> - <type> (****<last 4 digits>)
     * e.g.
     * barclays - Current account (****1234)
     * Natwest - ISA (****4444).
     *
     * @return string
     */
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('name_one_line')]
    #[JMS\Groups(['account'])]
    public function getNameOneLine(): string
    {
        return (!empty($this->getBank()) ? $this->getBank() . ' - ' : '')
            . $this->getAccountTypeText()
            . ' (****' . $this->getAccountNumber() . ')';
    }
}
