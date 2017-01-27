<?php
namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\MoneyShortCategory;

trait MoneyShortTrait
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
     * @param MoneyShortCategory[] $moneyShortCategories
     */
    public function setMoneyShortCategories($moneyShortCategories)
    {
        $this->moneyShortCategories = $moneyShortCategories;
    }

    /**
     * @param string $typeId
     *
     * @return StateBenefit
//     */
//    public function getStateBenefitByTypeId($typeId)
//    {
//        return $this->getStateBenefits()->filter(function (StateBenefit $sb) use ($typeId) {
//            return $sb->getTypeId() == $typeId;
//        })->first();
//    }
}
