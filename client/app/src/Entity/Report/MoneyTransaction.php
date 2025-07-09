<?php

namespace App\Entity\Report;

use App\Entity\Report\Traits\HasBankAccountTrait;
use App\Entity\User;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class MoneyTransaction
{
    use HasBankAccountTrait;

    public static function getCategoriesGrouped($typeFilter)
    {
        $ret = [];
        foreach (MoneyTransaction::$categories as $k => $row) {
            list($categoryId, $hasDetails, $groupId, $type) = $row;
            if ($type == $typeFilter) {
                if (!isset($ret[$groupId])) {
                    $ret[$groupId] = [];
                }
                $ret[$groupId][] = $categoryId;
            }
        }

        return $ret;
    }

    /**
     * Keep in sync with API
     * No need to do a separate call to get the list
     * Possible refactor would be moving some entities data into a shared library
     * ORDER is deprecated, re-order moving elements in the code.
     *
     * @JMS\Exclude
     */
    public static $categories = [
        // category | hasMoreDetails | order | group | type (in/out) | allowed users (optional)

        // Money In
        ['salary-or-wages', false, 'salary-or-wages', 'in'],

        ['account-interest', false, 'income-and-earnings', 'in'],
        ['dividends', false, 'income-and-earnings', 'in'],
        ['income-from-property-rental', false, 'income-and-earnings', 'in'],

        ['personal-pension', false, 'pensions', 'in'],
        ['state-pension', false, 'pensions', 'in'],

        ['attendance-allowance', false, 'state-benefits', 'in'],
        ['disability-living-allowance', false, 'state-benefits', 'in'],
        ['employment-support-allowance', false, 'state-benefits', 'in'],
        ['housing-benefit', false, 'state-benefits', 'in'],
        ['incapacity-benefit', false, 'state-benefits', 'in'],
        ['income-support', false, 'state-benefits', 'in'],
        ['pension-credit', false, 'state-benefits', 'in'],
        ['personal-independence-payment', false, 'state-benefits', 'in'],
        ['severe-disablement-allowance', false, 'state-benefits', 'in'],
        ['universal-credit', false, 'state-benefits', 'in'],
        ['winter-fuel-cold-weather-payment', false, 'state-benefits', 'in'],
        ['other-benefits', true, 'state-benefits', 'in'],

        ['compensation-or-damages-award', true, 'compensation-or-damages-award', 'in'],

        ['bequest-or-inheritance', false, 'one-off', 'in'],
        ['cash-gift-received', false, 'one-off', 'in'],
        ['refunds', false, 'one-off', 'in'],
        ['sale-of-asset', true, 'one-off', 'in'],
        ['sale-of-investment', true, 'one-off', 'in'],
        ['sale-of-property', true, 'one-off', 'in'],

        ['anything-else', true, 'moneyin-other', 'in'], // no group

        // Money Out
        ['care-fees', false, 'care-and-medical', 'out'],
        ['local-authority-charges-for-care', false, 'care-and-medical', 'out'],
        ['medical-expenses', false, 'care-and-medical', 'out'],
        ['medical-insurance', false, 'care-and-medical', 'out'],

        ['broadband', false, 'household-bills', 'out'],
        ['council-tax', false, 'household-bills', 'out'],
        ['telephone-and-broadband', false, 'household-bills', 'out'],
        ['dual-fuel', false, 'household-bills', 'out'],
        ['electricity', false, 'household-bills', 'out'],
        ['food', false, 'household-bills', 'out'],
        ['gas', false, 'household-bills', 'out'],
        ['insurance-eg-life-home-contents', false, 'household-bills', 'out'],
        ['property-maintenance-improvement', true, 'household-bills', 'out'],
        ['telephone', false, 'household-bills', 'out'],
        ['tv-services', false, 'household-bills', 'out'],
        ['water', false, 'household-bills', 'out'],

        ['accommodation-service-charge', false, 'accommodation', 'out'],
        ['mortgage', false, 'accommodation', 'out'],
        ['rent', false, 'accommodation', 'out'],

        ['client-transport-bus-train-taxi-fares', false, 'client-expenses', 'out'],
        ['clothes', false, 'client-expenses', 'out'],
        ['day-trips', false, 'client-expenses', 'out'],
        ['holidays', false, 'client-expenses', 'out'],
        ['personal-allowance-pocket-money', false, 'client-expenses', 'out'],
        ['toiletries', false, 'client-expenses', 'out'],

        ['deputy-security-bond', false, 'fees', 'out'],
        ['opg-fees', false, 'fees', 'out'],
        ['professional-fees-eg-solicitor-accountant', true, 'fees', 'out', User::ROLE_LAY_DEPUTY],
        ['professional-fees-eg-solicitor-accountant-non-lay', true, 'fees', 'out', User::ROLE_ORG],

        ['investment-bonds-purchased', true, 'major-purchases', 'out'],
        ['investment-account-purchased', true, 'major-purchases', 'out'],
        ['stocks-and-shares-purchased', true, 'major-purchases', 'out'],
        ['purchase-over-1000', true, 'major-purchases', 'out'],

        ['bank-charges', false, 'debt-and-charges', 'out'],
        ['credit-cards-charges', false, 'debt-and-charges', 'out'],
        ['loans', false, 'debt-and-charges', 'out'],
        ['tax-payments-to-hmrc', false, 'debt-and-charges', 'out'],
        ['unpaid-care-fees', false, 'debt-and-charges', 'out'],

        ['cash-withdrawn', true, 'moving-money', 'out'],

        ['transfers-out-to-other-accounts', true, 'moving-money', 'out'],

        ['anything-else-paid-out', true, 'moneyout-other', 'out'],
    ];

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"transaction"})
     */
    private string $id;

    /**
     * @JMS\Type("string")
     *
     * @Assert\NotBlank(message="moneyTransaction.form.category.notBlank", groups={"transaction-group"})
     */
    private string $group;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"transaction"})
     *
     * @Assert\NotBlank(message="moneyTransaction.form.category.notBlank", groups={"transaction-category"})
     */
    private ?string $category = null;

    /**
     * @JMS\Type("string")
     */
    private string $type;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"transaction"})
     *
     * @Assert\NotBlank(message="moneyTransaction.form.amount.notBlank", groups={"transaction-amount"})
     *
     * @Assert\Range(min=0.01, max=100000000000, notInRangeMessage = "moneyTransaction.form.amount.notInRangeMessage", groups={"transaction-amount"})
     */
    private string $amount;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"transaction"})
     *
     * @Assert\NotBlank(message="moneyTransaction.form.description.notBlank", groups={"transaction-description"})
     */
    private ?string $description = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function setGroup(string $group): static
    {
        $this->group = $group;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        if ($this->isValidCategory($category)) {
            $this->category = $category;
        }

        return $this;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Checks category is valid.
     */
    private function isValidCategory(?string $category = ''): bool
    {
        foreach (self::$categories as $cat) {
            list($categoryId, $hasDetails, $groupId, $type) = $cat;

            if (
                (($groupId === $categoryId) && $category == $groupId)
                || $category == $categoryId
            ) {
                return true;
            }
        }
        throw new \RuntimeException('Invalid category: '.$category);
    }

    /**
     * Get the type (in/out) based on the category.
     *
     * @JMS\VirtualProperty
     *
     * @JMS\SerializedName("type")
     *
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     */
    public function getType(): ?string
    {
        foreach (self::$categories as $cat) {
            list($categoryId, $hasDetails, $groupId, $type) = $cat;
            if ($this->getCategory() == $categoryId) {
                return $type;
            }
        }

        return null;
    }
}
