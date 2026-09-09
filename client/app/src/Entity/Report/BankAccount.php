<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\BankAccountInterface;
use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;
use Symfony\Component\Validator\Constraints as Assert;

class BankAccount implements BankAccountInterface
{
    use HasReportTrait;

    /**
     * Keep in sync with api.
     * @var string[] $types
     */
    public static array $types = [
        'current',
        'savings',
        'isa',
        'postoffice',
        'cfo',
        'other',
        'other_no_sortcode',
    ];

    /**
     * @var array<string> $typesNotRequiringSortCode
     */
    private static array $typesNotRequiringSortCode = [
        'postoffice',
        'cfo',
        'other_no_sortcode',
    ];

    /**
     * @var array<string> $typesNotRequiringBankName
     */
    private static array $typesNotRequiringBankName = [
        'postoffice',
        'cfo',
    ];

    #[JMS\Type('integer')]
    private ?int $id = null;

    #[JMS\Groups(['account'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'account.accountType.notBlank', groups: ['bank-account-type'])]
    #[Assert\Length(max: 100, maxMessage: 'account.accountType.maxMessage', groups: ['bank-account-type'])]
    private ?string $accountType = null;

    #[JMS\Groups(['account'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'account.bank.notBlank', groups: ['bank-account-name'])]
    #[Assert\Length(min: 2, max: 500, minMessage: 'account.bank.minMessage', maxMessage: 'account.bank.maxMessage', groups: ['bank-account-name'])]
    private ?string $bank = null;

    #[JMS\Type('string')]
    /** @phpstan-ignore property.unusedType */
    private ?string $accountTypeText = null;

    #[JMS\Groups(['account'])]
    #[JMS\Type('string')]
    #[Assert\NotBlank(message: 'account.accountNumber.notBlank', groups: ['bank-account-number'])]
    #[Assert\Type(type: 'alnum', message: 'account.accountNumber.type', groups: ['bank-account-number'])]
    #[Assert\Length(min: 4, max: 4, exactMessage: 'account.accountNumber.length', groups: ['bank-account-number'])]
    private ?string $accountNumber = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['account'])]
    private ?string $sortCode = null;

    #[JMS\Type('double')]
    #[JMS\Groups(['account'])]
    #[Assert\NotBlank(message: 'account.openingBalance.notBlank', groups: ['bank-account-opening-balance'])]
    #[Assert\Type(type: 'numeric', message: 'account.openingBalance.type', groups: ['bank-account-opening-balance'])]
    #[Assert\Range(maxMessage: 'account.openingBalance.outOfRange', max: 100000000000, groups: ['bank-account-opening-balance'])]
    private ?float $openingBalance = null;

    #[JMS\Type('double')]
    #[Assert\Type(type: 'numeric', message: 'account.closingBalance.type', groups: ['bank-account-closing-balance'])]
    #[Assert\Range(maxMessage: 'account.closingBalance.outOfRange', max: 100000000000, groups: ['bank-account-closing-balance'])]
    #[JMS\Groups(['account'])]
    private ?float $closingBalance = null;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['account'])]
    #[Assert\NotBlank(message: 'account.isClosed.notBlank', groups: ['bank-account-is-closed'])]
    private ?bool $isClosed = null;

    /**
     * @var ?string 'yes'|'no'|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['account'])]
    #[Assert\NotBlank(message: 'account.isJointAccount.notBlank', groups: ['bank-account-is-joint'])]
    private ?string $isJointAccount = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['account'])]
    private ?string $meta = null;

    /**
     * Get bank account name in one line. Comes from Virtual property.
     *
     * <bank> - <type> (****<last 4 digits>)
     * e.g. "barclays - Current account (****1234)"
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['account'])]
    private ?string $nameOneLine = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

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

    public function setSortCode(?string $sortCode): static
    {
        $this->sortCode = $sortCode;

        return $this;
    }

    public function getSortCode(): ?string
    {
        return $this->sortCode;
    }

    public function setAccountNumber(?string $accountNumber): static
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setOpeningBalance(?float $openingBalance): static
    {
        $this->openingBalance = $openingBalance;

        return $this;
    }

    public function getOpeningBalance(): ?float
    {
        return $this->openingBalance;
    }

    public function setClosingBalance(?float $closingBalance): static
    {
        $this->closingBalance = $closingBalance;

        if (!$this->isClosingBalanceZero()) {
            $this->setIsClosed(false);
        }

        return $this;
    }

    public function getClosingBalance(): ?float
    {
        if (!is_numeric($this->closingBalance)) {
            return null;
        }

        return round(floatval($this->closingBalance), 2);
    }

    public function isClosingBalanceZero(): bool
    {
        return !is_null($this->closingBalance) && $this->getClosingBalance() === 0.0;
    }

    public function hasClosingBalance(): bool
    {
        if (is_null($this->closingBalance)) {
            return false;
        }

        return true;
    }

    public function getIsClosed(): ?bool
    {
        return $this->isClosed;
    }

    public function setIsClosed(bool $isClosed): static
    {
        $this->isClosed = $isClosed;

        return $this;
    }

    public function getAccountType(): ?string
    {
        return $this->accountType;
    }

    public function getAccountTypeText(): ?string
    {
        return $this->accountTypeText;
    }

    /**
     * Sort code required.
     */
    public function requiresSortCode(): bool
    {
        return !in_array($this->getAccountType(), self::$typesNotRequiringSortCode);
    }

    /**
     * Bank name required.
     */
    public function requiresBankName(): bool
    {
        return !in_array($this->getAccountType(), self::$typesNotRequiringBankName);
    }

    public function setAccountType(?string $accountType): void
    {
        $this->accountType = $accountType;
    }

    public function getIsJointAccount(): ?string
    {
        return $this->isJointAccount;
    }

    public function setIsJointAccount(?string $isJointAccount): static
    {
        $this->isJointAccount = $isJointAccount;

        return $this;
    }

    public function getMeta(): ?string
    {
        return $this->meta;
    }

    public function setMeta(?string $meta): static
    {
        $this->meta = $meta;

        return $this;
    }

    public function getNameOneLine(): ?string
    {
        return $this->nameOneLine;
    }

    public function setNameOneLine(?string $nameOneLine): static
    {
        $this->nameOneLine = $nameOneLine;

        return $this;
    }

    /**
     * Format the account name for CSV.
     */
    public function getDisplayName(): ?string
    {
        return match ($this->getAccountType()) {
            'current' => ($this->getIsJointAccount() ? 'Joint current ' : 'Current') . ' account (****' . $this->getAccountNumber() . ' / ' . $this->getDisplaySortCode() . ')',
            'savings' => ($this->getIsJointAccount() ? 'Joint savings ' : 'Savings') . ' account (****' . $this->getAccountNumber() . ' / ' . $this->getDisplaySortCode() . ')',
            'isa' => ($this->getIsJointAccount() ? 'Joint ISA ' : 'ISA') . ' (****' . $this->getAccountNumber() . ' / ' . $this->getDisplaySortCode() . ')',
            'postoffice' => ($this->getIsJointAccount() ? 'Joint Post office ' : 'Post office') . ' account (****' . $this->getAccountNumber() . ')',
            'cfo' => ($this->getIsJointAccount() ? 'Joint Court funds ' : 'Court funds') . ' account (****' . $this->getAccountNumber() . ')',
            'other' => ($this->getIsJointAccount() ? 'Joint other ' : 'Other') . ' account ' . ' (****' . $this->getAccountNumber() . ' / ' . $this->getDisplaySortCode() . ')',
            'other_no_sortcode' => ($this->getIsJointAccount() ? 'Joint other ' : 'Other') . ' account ' . ' (****' . $this->getAccountNumber() . ')',
            default => null,
        };
    }

    /**
     * Formats a sort code with hyphens.
     */
    public function getDisplaySortCode(): ?string
    {
        $sortCode = $this->getSortCode();

        if ($sortCode !== null && strlen($sortCode) == 6) {
            $sc = str_split($sortCode);
            return $sc[0] . $sc[1] . '-' . $sc[2] . $sc[3] . '-' . $sc[4] . $sc[5];
        }

        return $this->getSortCode();
    }
}
