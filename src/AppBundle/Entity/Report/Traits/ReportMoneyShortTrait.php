<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\MoneyShortCategory;
use AppBundle\Entity\Report\MoneyTransactionShort;
use JMS\Serializer\Annotation as JMS;

trait ReportMoneyShortTrait
{
    /**
     * @var MoneyShortCategory[]
     *
     * @JMS\Groups({"money-short-categories-in"})
     * @JMS\Type("array<AppBundle\Entity\Report\MoneyShortCategory>")
     */
    private $moneyShortCategoriesIn;

    /**
     * @var MoneyShortCategory[]
     *
     * @JMS\Groups({"money-short-categories-out"})
     * @JMS\Type("array<AppBundle\Entity\Report\MoneyShortCategory>")
     */
    private $moneyShortCategoriesOut;

    /**
     * @var MoneyTransactionShort[]
     */
    private $moneyTransactionsShort;

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
     */
    private $moneyTransactionsShortOutExist;

    /**
     * @return \AppBundle\Entity\Report\MoneyShortCategory[]
     */
    public function getMoneyShortCategoriesIn()
    {
        return $this->moneyShortCategoriesIn;
    }

    /**
     * @param \AppBundle\Entity\Report\MoneyShortCategory[] $moneyShortCategoriesIn
     */
    public function setMoneyShortCategoriesIn($moneyShortCategoriesIn)
    {
        $this->moneyShortCategoriesIn = $moneyShortCategoriesIn;
    }

    /**
     * @return \AppBundle\Entity\Report\MoneyShortCategory[]
     */
    public function getMoneyShortCategoriesOut()
    {
        return $this->moneyShortCategoriesOut;
    }

    /**
     * @param \AppBundle\Entity\Report\MoneyShortCategory[] $moneyShortCategoriesOut
     */
    public function setMoneyShortCategoriesOut($moneyShortCategoriesOut)
    {
        $this->moneyShortCategoriesOut = $moneyShortCategoriesOut;
    }

    /**
     * Return element of array that have isPresent() = true
     *
     * @param array $elements
     *
     * @return int
     */
    public function recordsPresent($elements)
    {
        if (empty($elements) || !is_array($elements)) {
            return [];
        }

        return array_filter($elements, function ($st) {
            return method_exists($st, 'isPresent') && $st->isPresent();
        });
    }

    /**
     * @return \AppBundle\Entity\Report\MoneyTransactionShort[]
     */
    public function getMoneyTransactionsShort()
    {
        return $this->moneyTransactionsShort;
    }

    /**
     * @param \AppBundle\Entity\Report\MoneyTransactionShort[] $moneyTransactionsShort
     */
    public function setMoneyTransactionsShort($moneyTransactionsShort)
    {
        $this->moneyTransactionsShort = $moneyTransactionsShort;
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

}
