<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\MoneyShortCategory;
use OPG\Digideps\Frontend\Entity\Report\MoneyTransactionShort;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportMoneyShortTrait
{
    /**
     * @var array<MoneyShortCategory>
     */
    #[JMS\Groups(['moneyShortCategoriesIn'])]
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\MoneyShortCategory>')]
    private array $moneyShortCategoriesIn = [];

    /**
     * @var array<MoneyShortCategory>
     */
    #[JMS\Groups(['moneyShortCategoriesOut'])]
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\MoneyShortCategory>')]
    private array $moneyShortCategoriesOut = [];

    /**
     * @var array<MoneyTransactionShort>
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\MoneyTransactionShort>')]
    private array $moneyTransactionsShortIn = [];

    /**
     * @var array<MoneyTransactionShort>
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\MoneyTransactionShort>')]
    private array $moneyTransactionsShortOut = [];

    /**
     * @var ?string 'yes'|'no'|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['money-transactions-short-in-exist'])]
    #[Assert\NotBlank(message: 'moneyTransactionShort.exist.notBlank', groups: ['exist'])]
    private ?string $moneyTransactionsShortInExist = null;

    /**
     * @var ?string 'yes'|'no'|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['money-transactions-short-out-exist'])]
    #[Assert\NotBlank(message: 'moneyTransactionShort.exist.notBlank', groups: ['exist'])]
    private ?string $moneyTransactionsShortOutExist = null;

    /**
     * @return array<MoneyShortCategory>
     */
    public function getMoneyShortCategoriesIn(): array
    {
        return $this->moneyShortCategoriesIn;
    }

    /**
     * @return array<MoneyShortCategory>
     */
    public function getMoneyShortCategoriesInPresent(): array
    {
        return array_filter($this->moneyShortCategoriesIn ?: [], function ($st): bool {
            return method_exists($st, 'isPresent') && $st->isPresent();
        });
    }

    /**
     * @param array<MoneyShortCategory> $moneyShortCategoriesIn
     */
    public function setMoneyShortCategoriesIn(array $moneyShortCategoriesIn): static
    {
        $this->moneyShortCategoriesIn = $moneyShortCategoriesIn;

        return $this;
    }

    /**
     * @return array<MoneyShortCategory>
     */
    public function getMoneyShortCategoriesOut(): array
    {
        return $this->moneyShortCategoriesOut;
    }

    /**
     * @return array<MoneyShortCategory>
     */
    public function getMoneyShortCategoriesOutPresent(): array
    {
        return array_filter($this->moneyShortCategoriesOut ?: [], function ($st): bool {
            return method_exists($st, 'isPresent') && $st->isPresent();
        });
    }

    /**
     * @param array<MoneyShortCategory> $moneyShortCategoriesOut
     */
    public function setMoneyShortCategoriesOut(array $moneyShortCategoriesOut): static
    {
        $this->moneyShortCategoriesOut = $moneyShortCategoriesOut;

        return $this;
    }

    /**
     * @return array<MoneyTransactionShort>
     */
    public function getMoneyTransactionsShortIn(): array
    {
        return $this->moneyTransactionsShortIn;
    }

    /**
     * @param array<MoneyTransactionShort> $moneyTransactionsShortIn
     */
    public function setMoneyTransactionsShortIn(array $moneyTransactionsShortIn): static
    {
        $this->moneyTransactionsShortIn = $moneyTransactionsShortIn;

        return $this;
    }

    /**
     * @return array<MoneyTransactionShort>
     */
    public function getMoneyTransactionsShortOut()
    {
        return $this->moneyTransactionsShortOut;
    }

    /**
     * @param array<MoneyTransactionShort> $moneyTransactionsShortOut
     */
    public function setMoneyTransactionsShortOut(array $moneyTransactionsShortOut): static
    {
        $this->moneyTransactionsShortOut = $moneyTransactionsShortOut;

        return $this;
    }

    public function getMoneyTransactionsShortInExist(): ?string
    {
        return $this->moneyTransactionsShortInExist;
    }

    public function setMoneyTransactionsShortInExist(?string $moneyTransactionsShortInExist): static
    {
        $this->moneyTransactionsShortInExist = $moneyTransactionsShortInExist;

        return $this;
    }

    public function getMoneyTransactionsShortOutExist(): ?string
    {
        return $this->moneyTransactionsShortOutExist;
    }

    public function setMoneyTransactionsShortOutExist(?string $moneyTransactionsShortOutExist): static
    {
        $this->moneyTransactionsShortOutExist = $moneyTransactionsShortOutExist;

        return $this;
    }

    /**
     * @param array<MoneyTransactionShort> $records
     */
    public function getTotalValue(array $records): float
    {
        $ret = 0.0;
        foreach ($records as $expense) {
            $ret += $expense->getAmount() ?? 0.0;
        }
        return $ret;
    }
}
