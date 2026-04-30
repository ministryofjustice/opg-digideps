<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Report\MoneyShortCategory;
use OPG\Digideps\Backend\Entity\Report\MoneyTransactionShort;
use OPG\Digideps\Backend\Entity\Report\MoneyTransactionShortIn;
use OPG\Digideps\Backend\Entity\Report\MoneyTransactionShortOut;

trait MoneyShortTrait
{
    /**
     * @var Collection<int, MoneyShortCategory>
     */
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: MoneyShortCategory::class, cascade: ['persist'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $moneyShortCategories;

    /**
     * @var Collection<int, MoneyTransactionShort>
     */
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: MoneyTransactionShort::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $moneyTransactionsShort;

    /**
     * @var ?string 'yes'|'no'|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['moneyShortCategoriesIn'])]
    #[ORM\Column(name: 'money_transactions_short_in_exist', type: 'string', length: 3, nullable: true)]
    private ?string $moneyTransactionsShortInExist = null;

    /**
     * @var ?string 'yes'|'no'|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['moneyShortCategoriesOut'])]
    #[ORM\Column(name: 'money_transactions_short_out_exist', type: 'string', length: 3, nullable: true)]
    private ?string $moneyTransactionsShortOutExist = null;

    /**
     * @return Collection<int, MoneyShortCategory>
     */
    public function getMoneyShortCategories(): Collection
    {
        return $this->moneyShortCategories;
    }

    /**
     * @return Collection<int, MoneyShortCategory>
     */
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('money_short_categories_in')]
    #[JMS\Groups(['moneyShortCategoriesIn'])]
    public function getMoneyShortCategoriesIn(): Collection
    {
        return $this->moneyShortCategories->filter(function ($e) {
            return 'in' == $e->getType();
        });
    }

    /**
     * @return Collection<int, MoneyShortCategory>
     */
    public function getMoneyShortCategoriesInPresent(): Collection
    {
        return $this->getMoneyShortCategories()->filter(function ($e) {
            return 'in' == $e->getType() && $e->getPresent();
        });
    }

    /**
     * @return Collection<int, MoneyShortCategory>
     */
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('money_short_categories_out')]
    #[JMS\Groups(['moneyShortCategoriesOut'])]
    public function getMoneyShortCategoriesOut(): Collection
    {
        return $this->getMoneyShortCategories()->filter(function ($e) {
            return 'out' == $e->getType();
        });
    }

    /**
     * @return Collection<int, MoneyShortCategory>
     */
    public function getMoneyShortCategoriesOutPresent(): Collection
    {
        return $this->getMoneyShortCategories()->filter(function ($e) {
            return 'out' == $e->getType() && $e->getPresent();
        });
    }

    /**
     *  @param Collection<int, MoneyShortCategory> $moneyShortCategories
     */
    public function setMoneyShortCategories(Collection $moneyShortCategories)
    {
        $this->moneyShortCategories = $moneyShortCategories;
    }

    /**
     * @return ?MoneyShortCategory
     */
    public function getMoneyShortCategoryByTypeId(string $typeId)
    {
        $categories = $this->moneyShortCategories->filter(function ($e) use ($typeId) {
            return $e->getTypeId() == $typeId;
        });

        return $categories->first() ?: null;
    }

    /**
     *  @return Collection<int, MoneyTransactionShort>
     */
    public function getMoneyTransactionsShort(): Collection
    {
        return $this->moneyTransactionsShort;
    }

    /**
     * @param Collection<int, MoneyTransactionShort> $moneyTransactionsShort
     */
    public function setMoneyTransactionsShort(Collection $moneyTransactionsShort): void
    {
        $this->moneyTransactionsShort = $moneyTransactionsShort;
    }

    /**
     * @return Collection<int, MoneyTransactionShort>
     */
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('money_transactions_short_in')]
    #[JMS\Groups(['moneyTransactionsShortIn'])]
    public function getMoneyTransactionsShortIn(): Collection
    {
        return $this->moneyTransactionsShort->filter(function ($t) {
            return $t instanceof MoneyTransactionShortIn;
        });
    }

    /**
     * @return Collection<int, MoneyTransactionShort>
     */
    #[JMS\VirtualProperty]
    #[JMS\SerializedName('money_transactions_short_out')]
    #[JMS\Groups(['moneyTransactionsShortOut'])]
    public function getMoneyTransactionsShortOut(): Collection
    {
        return $this->moneyTransactionsShort->filter(function ($t) {
            return $t instanceof MoneyTransactionShortOut;
        });
    }

    public function getMoneyTransactionsShortInExist(): ?string
    {
        return $this->moneyTransactionsShortInExist;
    }

    public function setMoneyTransactionsShortInExist(?string $moneyTransactionsShortInExist): void
    {
        $this->moneyTransactionsShortInExist = $moneyTransactionsShortInExist;
    }

    public function getMoneyTransactionsShortOutExist(): ?string
    {
        return $this->moneyTransactionsShortOutExist;
    }

    public function setMoneyTransactionsShortOutExist(string $moneyTransactionsShortOutExist): void
    {
        $this->moneyTransactionsShortOutExist = $moneyTransactionsShortOutExist;
    }
}
