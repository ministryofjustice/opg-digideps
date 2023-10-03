<?php

namespace App\Entity\Report\Traits;

use App\Entity\Report\MoneyShortCategory;
use App\Entity\Report\MoneyTransactionShort;
use JMS\Serializer\Annotation as JMS;

trait ReportMoneyShortTrait
{
    /**
     * @var MoneyShortCategory[]
     *
     * @JMS\Groups({"moneyShortCategoriesIn"})
     * @JMS\Type("array<App\Entity\Report\MoneyShortCategory>")
     */
    private $moneyShortCategoriesIn = [];

    /**
     * @var MoneyShortCategory[]
     *
     * @JMS\Groups({"moneyShortCategoriesOut"})
     * @JMS\Type("array<App\Entity\Report\MoneyShortCategory>")
     */
    private $moneyShortCategoriesOut = [];

    /**
     * @var MoneyTransactionShort[]
     *
     * @JMS\Type("array<App\Entity\Report\MoneyTransactionShort>")
     */
    private $moneyTransactionsShortIn = [];

    /**
     * @var MoneyTransactionShort[]
     *
     * @JMS\Type("array<App\Entity\Report\MoneyTransactionShort>")
     */
    private $moneyTransactionsShortOut = [];

    /**
     * @var string yes|no|null
     *
     * @JMS\Type("string")
     * @JMS\Groups({"money-transactions-short-in-exist"})
     *
     * @Assert\NotBlank(message="moneyTransactionShort.exist.notBlank", groups={"exist"})
     */
    private $moneyTransactionsShortInExist;

    /**
     * @var string yes|no|null
     *
     * @JMS\Type("string")
     * @JMS\Groups({"money-transactions-short-out-exist"})
     *
     * @Assert\NotBlank(message="moneyTransactionShort.exist.notBlank", groups={"exist"})
     */
    private $moneyTransactionsShortOutExist;

    /**
     * @return \App\Entity\Report\MoneyShortCategory[]
     */
    public function getMoneyShortCategoriesIn()
    {
        return $this->moneyShortCategoriesIn;
    }

    /**
     * @return \App\Entity\Report\MoneyShortCategory[]
     */
    public function getMoneyShortCategoriesInPresent()
    {
        return array_filter($this->moneyShortCategoriesIn ?: [], function ($st) {
            return method_exists($st, 'isPresent') && $st->isPresent();
        });
    }

    /**
     * @param \App\Entity\Report\MoneyShortCategory[] $moneyShortCategoriesIn
     */
    public function setMoneyShortCategoriesIn($moneyShortCategoriesIn)
    {
        $this->moneyShortCategoriesIn = $moneyShortCategoriesIn;
    }

    /**
     * @return \App\Entity\Report\MoneyShortCategory[]
     */
    public function getMoneyShortCategoriesOut()
    {
        return $this->moneyShortCategoriesOut;
    }

    /**
     * @return \App\Entity\Report\MoneyShortCategory[]
     */
    public function getMoneyShortCategoriesOutPresent()
    {
        return array_filter($this->moneyShortCategoriesOut ?: [], function ($st) {
            return method_exists($st, 'isPresent') && $st->isPresent();
        });
    }

    /**
     * @param \App\Entity\Report\MoneyShortCategory[] $moneyShortCategoriesOut
     */
    public function setMoneyShortCategoriesOut($moneyShortCategoriesOut)
    {
        $this->moneyShortCategoriesOut = $moneyShortCategoriesOut;
    }

    /**
     * @return \App\Entity\Report\MoneyTransactionShort[]
     */
    public function getMoneyTransactionsShortIn()
    {
        return $this->moneyTransactionsShortIn;
    }

    /**
     * @param \App\Entity\Report\MoneyTransactionShort[] $moneyTransactionsShortIn
     */
    public function setMoneyTransactionsShortIn($moneyTransactionsShortIn)
    {
        $this->moneyTransactionsShortIn = $moneyTransactionsShortIn;
    }

    /**
     * @return mixed
     */
    public function getMoneyTransactionsShortOut()
    {
        return $this->moneyTransactionsShortOut;
    }

    /**
     * @param mixed $moneyTransactionsShortOut
     */
    public function setMoneyTransactionsShortOut($moneyTransactionsShortOut)
    {
        $this->moneyTransactionsShortOut = $moneyTransactionsShortOut;
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
    public function setMoneyTransactionsShortInExist($moneyTransactionsShortInExist)
    {
        $this->moneyTransactionsShortInExist = $moneyTransactionsShortInExist;
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
    public function setMoneyTransactionsShortOutExist($moneyTransactionsShortOutExist)
    {
        $this->moneyTransactionsShortOutExist = $moneyTransactionsShortOutExist;
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
