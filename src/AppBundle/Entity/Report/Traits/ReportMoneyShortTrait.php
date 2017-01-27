<?php
namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\MoneyShortCategory;

trait ReportMoneyShortTrait
{
    /**
     * @var MoneyShortCategory[]
     *
     * @JMS\Groups({"money-short-categories"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\MoneyShortCategory", mappedBy="report")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $moneyShortCategories;

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
     * @JMS\Groups({"money-short-categories-in"})
     */
    public function getMoneyShortCategoriesIn()
    {
        return $this->moneyShortCategories->filter(function($e){
            return $e->getType() == 'in';
        });
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("money_short_categories_out")
     * @JMS\Groups({"money-short-categories-out"})
     */
    public function getMoneyShortCategoriesOut()
    {
        return $this->moneyShortCategories->filter(function($e){
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
}
