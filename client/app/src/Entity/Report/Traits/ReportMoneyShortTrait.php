<?php

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use OPG\Digideps\Frontend\Entity\Report\MoneyShortCategory;
use OPG\Digideps\Frontend\Entity\Report\MoneyTransactionShort;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportMoneyShortTrait
{
    /**
     * @var MoneyShortCategory[]
     */
    #[JMS\Groups(['moneyShortCategoriesIn'])]
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\MoneyShortCategory>')]
    private array $moneyShortCategoriesIn = [];

    /**
     * @var MoneyShortCategory[]
     */
    #[JMS\Groups(['moneyShortCategoriesOut'])]
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\MoneyShortCategory>')]
    private array $moneyShortCategoriesOut = [];

    /**
     * @var MoneyTransactionShort[]
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\MoneyTransactionShort>')]
    private array $moneyTransactionsShortIn = [];

    /**
     * @var MoneyTransactionShort[]
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\MoneyTransactionShort>')]
    private array $moneyTransactionsShortOut = [];

    /**
     * @var string 'yes'|'no'|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['money-transactions-short-in-exist'])]
    #[Assert\NotBlank(message: 'moneyTransactionShort.exist.notBlank', groups: ['exist'])]
    private $moneyTransactionsShortInExist;

    /**
     * @var string 'yes'|'no'|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['money-transactions-short-out-exist'])]
    #[Assert\NotBlank(message: 'moneyTransactionShort.exist.notBlank', groups: ['exist'])]
    private $moneyTransactionsShortOutExist;

    /**
     * @return MoneyShortCategory[]
     */
    public function getMoneyShortCategoriesIn(): array
    {
        return $this->moneyShortCategoriesIn;
    }

    /**
     * @return MoneyShortCategory[]
     */
    public function getMoneyShortCategoriesInPresent(): array
    {
        return array_filter($this->moneyShortCategoriesIn ?: [], function ($st): bool {
            return method_exists($st, 'isPresent') && $st->isPresent();
        });
    }

    /**
     * @param MoneyShortCategory[] $moneyShortCategoriesIn
     */
    public function setMoneyShortCategoriesIn(array $moneyShortCategoriesIn): static
    {
        $this->moneyShortCategoriesIn = $moneyShortCategoriesIn;

        return $this;
    }

    /**
     * @return MoneyShortCategory[]
     */
    public function getMoneyShortCategoriesOut(): array
    {
        return $this->moneyShortCategoriesOut;
    }

    /**
     * @return MoneyShortCategory[]
     */
    public function getMoneyShortCategoriesOutPresent(): array
    {
        return array_filter($this->moneyShortCategoriesOut ?: [], function ($st): bool {
            return method_exists($st, 'isPresent') && $st->isPresent();
        });
    }

    /**
     * @param MoneyShortCategory[] $moneyShortCategoriesOut
     */
    public function setMoneyShortCategoriesOut(array $moneyShortCategoriesOut): static
    {
        $this->moneyShortCategoriesOut = $moneyShortCategoriesOut;

        return $this;
    }

    /**
     * @return MoneyTransactionShort[]
     */
    public function getMoneyTransactionsShortIn(): array
    {
        return $this->moneyTransactionsShortIn;
    }

    /**
     * @param MoneyTransactionShort[] $moneyTransactionsShortIn
     */
    public function setMoneyTransactionsShortIn(array $moneyTransactionsShortIn): static
    {
        $this->moneyTransactionsShortIn = $moneyTransactionsShortIn;

        return $this;
    }

    /**
     * @return MoneyTransactionShort[]
     */
    public function getMoneyTransactionsShortOut()
    {
        return $this->moneyTransactionsShortOut;
    }

    /**
     * @param MoneyTransactionShort[] $moneyTransactionsShortOut
     */
    public function setMoneyTransactionsShortOut(array $moneyTransactionsShortOut): static
    {
        $this->moneyTransactionsShortOut = $moneyTransactionsShortOut;

        return $this;
    }

    /**
     * @return string
     */
    public function getMoneyTransactionsShortInExist()
    {
        return $this->moneyTransactionsShortInExist;
    }

    /**
     * @param string $moneyTransactionsShortInExist
     */
    public function setMoneyTransactionsShortInExist($moneyTransactionsShortInExist): static
    {
        $this->moneyTransactionsShortInExist = $moneyTransactionsShortInExist;

        return $this;
    }

    /**
     * @return string
     */
    public function getMoneyTransactionsShortOutExist()
    {
        return $this->moneyTransactionsShortOutExist;
    }

    /**
     * @param string $moneyTransactionsShortOutExist
     */
    public function setMoneyTransactionsShortOutExist($moneyTransactionsShortOutExist): static
    {
        $this->moneyTransactionsShortOutExist = $moneyTransactionsShortOutExist;

        return $this;
    }

    /**
     * @param  MoneyTransactionShort[] $records
     * @return int
     */
    public function getTotalValue(array $records)
    {
        $ret = 0;
        foreach ($records as $expense) {
            $ret += $expense->getAmount();
        }

        return $ret;
    }
}
