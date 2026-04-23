<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use OPG\Digideps\Backend\Entity\Report\MoneyShortCategory;
use OPG\Digideps\Backend\Entity\Report\MoneyTransactionShort;
use OPG\Digideps\Backend\Entity\Report\MoneyTransactionShortIn;
use OPG\Digideps\Backend\Entity\Report\MoneyTransactionShortOut;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait MoneyShortTrait
{
    /**
     * @var MoneyShortCategory[]
     */
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: MoneyShortCategory::class, cascade: ['persist'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $moneyShortCategories;

    /**
     * @var MoneyTransactionShort[]
     */
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: MoneyTransactionShort::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $moneyTransactionsShort;

    /**
     * @var string yes|no|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['moneyShortCategoriesIn'])]
    #[ORM\Column(name: 'money_transactions_short_in_exist', type: 'string', length: 3, nullable: true)]
    private $moneyTransactionsShortInExist;

    /**
     * @var string yes|no|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['moneyShortCategoriesOut'])]
    #[ORM\Column(name: 'money_transactions_short_out_exist', type: 'string', length: 3, nullable: true)]
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
    public function getMoneyShortCategoriesIn(): array
    {
        return array_filter($this->moneyShortCategories, function ($e) {
            return 'in' == $e->getType();
        });
    }

    /**
     * @return MoneyShortCategory[]
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
     * @return MoneyShortCategory[]
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
     * @return ?MoneyShortCategory
     */
    public function getMoneyShortCategoryByTypeId($typeId)
    {
        $categories = array_filter($this->moneyShortCategories, function ($e) use ($typeId) {
            return $e->getTypeId() == $typeId;
        });

        return $categories[0] ?? null;
    }

    /**
     * @return MoneyTransactionShort[]
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
