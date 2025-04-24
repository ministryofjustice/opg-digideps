<?php

namespace App\Entity\Report\Traits;

use App\Entity\Report\MoneyShortCategory;
use App\Entity\Report\MoneyTransactionShort;
use App\Entity\Report\MoneyTransactionShortIn;
use App\Entity\Report\MoneyTransactionShortOut;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

trait MoneyShortTrait
{
    /**
     * @var MoneyShortCategory[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Report\MoneyShortCategory", mappedBy="report", cascade={"persist"})
     *
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $moneyShortCategories;

    /**
     * @var MoneyTransactionShort[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Report\MoneyTransactionShort", mappedBy="report", cascade={"persist", "remove"})
     *
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $moneyTransactionsShort;

    /**
     * @var string yes|no|null
     *
     *
     *
     * @ORM\Column(name="money_transactions_short_in_exist", type="string", length=3, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['moneyShortCategoriesIn'])]
    private $moneyTransactionsShortInExist;

    /**
     * @var string yes|no|null
     *
     *
     *
     * @ORM\Column(name="money_transactions_short_out_exist", type="string", length=3, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['moneyShortCategoriesOut'])]
    private $moneyTransactionsShortOutExist;

    /**
     * @return MoneyShortCategory[]
     */
    public function getMoneyShortCategories()
    {
        return $this->moneyShortCategories;
    }

    
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('money_short_categories_in')]
    #[JMS\Groups(['moneyShortCategoriesIn'])]
    public function getMoneyShortCategoriesIn()
    {
        return $this->getMoneyShortCategories()->filter(function ($e) {
            return 'in' == $e->getType();
        });
    }

    /**
     * @return \App\Entity\Report\MoneyShortCategory[]
     */
    public function getMoneyShortCategoriesInPresent()
    {
        return $this->getMoneyShortCategories()->filter(function ($e) {
            return 'in' == $e->getType() && $e->getPresent();
        });
    }

    
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('money_short_categories_out')]
    #[JMS\Groups(['moneyShortCategoriesOut'])]
    public function getMoneyShortCategoriesOut()
    {
        return $this->getMoneyShortCategories()->filter(function ($e) {
            return 'out' == $e->getType();
        });
    }

    /**
     * @return \App\Entity\Report\MoneyShortCategory[]
     */
    public function getMoneyShortCategoriesOutPresent()
    {
        return $this->getMoneyShortCategories()->filter(function ($e) {
            return 'out' == $e->getType() && $e->getPresent();
        });
    }

    /**
     * @param MoneyShortCategory[] $moneyShortCategories
     */
    public function setMoneyShortCategories($moneyShortCategories)
    {
        $this->moneyShortCategories = $moneyShortCategories;
    }

    /**
     * @param string $typeId
     *
     * @return MoneyShortCategory
     */
    public function getMoneyShortCategoryByTypeId($typeId)
    {
        return $this->moneyShortCategories->filter(function ($e) use ($typeId) {
            return $e->getTypeId() == $typeId;
        })->first();
    }

    /**
     * @return \App\Entity\Report\MoneyTransactionShort[]
     */
    public function getMoneyTransactionsShort()
    {
        return $this->moneyTransactionsShort;
    }

    /**
     * @param ArrayCollection $moneyTransactionsShort
     */
    public function setMoneyTransactionsShort($moneyTransactionsShort)
    {
        $this->moneyTransactionsShort = $moneyTransactionsShort;
    }

    /**
     *
     *
     *
     * @return MoneyTransactionShort[]
     */
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('money_transactions_short_in')]
    #[JMS\Groups(['moneyTransactionsShortIn'])]
    public function getMoneyTransactionsShortIn()
    {
        return $this->moneyTransactionsShort->filter(function ($t) {
            return $t instanceof MoneyTransactionShortIn;
        });
    }

    /**
     *
     *
     *
     * @return MoneyTransactionShort[]
     */
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('money_transactions_short_out')]
    #[JMS\Groups(['moneyTransactionsShortOut'])]
    public function getMoneyTransactionsShortOut()
    {
        return $this->moneyTransactionsShort->filter(function ($t) {
            return $t instanceof MoneyTransactionShortOut;
        });
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
