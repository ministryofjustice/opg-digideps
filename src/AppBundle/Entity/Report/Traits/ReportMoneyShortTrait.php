<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\MoneyShortCategory;
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


}
