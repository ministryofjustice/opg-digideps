<?php
namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\MoneyShortCategory;
use AppBundle\Entity\Report\MoneyTransactionShort;
use AppBundle\Entity\Report\MoneyTransactionShortIn;
use AppBundle\Entity\Report\MoneyTransactionShortOut;

trait ReportMoneyShortTrait
{
    /**
     * @var MoneyShortCategory[]
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\MoneyShortCategory", mappedBy="report")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $moneyShortCategories;

    /**
     * @var MoneyTransactionShort[]
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\MoneyTransactionShort", mappedBy="report", cascade={"persist"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $moneyTransactionsShort;

    /**
     * @var string yes|no|null
     *
     * @JMS\Type("string")
     * @JMS\Groups({"moneyShortCategoriesIn"})
     * @ORM\Column(name="money_transactions_short_in_exist", type="string", length=3, nullable=true)
     */
    private $moneyTransactionsShortInExist;

    /**
     * @var string yes|no|null
     *
     * @JMS\Type("string")
     * @JMS\Groups({"moneyShortCategoriesOut"})
     * @ORM\Column(name="money_transactions_short_out_exist", type="string", length=3, nullable=true)
     */
    private $moneyTransactionsShortOutExist;

    /**
     * @return MoneyShortCategory[]
     */
    public function getMoneyShortCategories()
    {
        return $this->moneyShortCategories;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("money_short_categories_in")
     * @JMS\Groups({"moneyShortCategoriesIn"})
     */
    public function getMoneyShortCategoriesIn()
    {
        return $this->moneyShortCategories->filter(function ($e) {
            return $e->getType() == 'in';
        });
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("money_short_categories_out")
     * @JMS\Groups({"moneyShortCategoriesOut"})
     */
    public function getMoneyShortCategoriesOut()
    {
        return $this->moneyShortCategories->filter(function ($e) {
            return $e->getType() == 'out';
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
     * @return \AppBundle\Entity\Report\MoneyTransactionShort[]
     */
    public function getMoneyTransactionsShort()
    {
        return $this->moneyTransactionsShort;
    }

    /**
     *
     * @JMS\VirtualProperty
     * @JMS\SerializedName("money_transactions_short_in")
     * @JMS\Groups({"moneyTransactionsShortIn"})
     *
     * @return MoneyTransactionShort[]
     */
    public function getMoneyTransactionsShortIn()
    {
        return $this->moneyTransactionsShort->filter(function ($t) {
            return $t instanceof MoneyTransactionShortIn;
        });
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("money_transactions_short_out")
     * @JMS\Groups({"moneyTransactionsShortOut"})
     *
     * @return MoneyTransactionShort[]
     */
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
