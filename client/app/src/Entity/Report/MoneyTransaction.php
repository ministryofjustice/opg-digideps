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
     * @JMS\Groups({"transaction"})
     */
    private $id;

    /**
     * @JMS\Type("string")
     * @Assert\NotBlank(message="moneyTransaction.form.category.notBlank", groups={"transaction-group"})
     */
    private $group;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"transaction"})
     * @Assert\NotBlank(message="moneyTransaction.form.category.notBlank", groups={"transaction-category"})
     */
    private $category;

    /**
     * @JMS\Type("string")
     */
    private $type;

    /**
     * @var array
     *
     * @JMS\Type("string")
     * @JMS\Groups({"transaction"})
     * @Assert\NotBlank(message="moneyTransaction.form.amount.notBlank", groups={"transaction-amount"})
     * @Assert\Range(min=0.01, max=100000000000, notInRangeMessage = "moneyTransaction.form.amount.notInRangeMessage", groups={"transaction-amount"})
     */
    private $amount;

    /**
     * @var string
     *
     * @JMS\Groups({"transaction"})
     * @Assert\NotBlank(message="moneyTransaction.form.description.notBlank", groups={"transaction-description"})
     * @JMS\Type("string")
     */
    private $description;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param mixed $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        if (MoneyTransaction::isValidCategory($category)) {
            $this->category = $category;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param array $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Checks category is valid.
     *
     * @param string $category
     *
     * @return bool
     */
    public static function isValidCategory($category = '')
    {
        foreach (self::$categories as $cat) {
            list($categoryId, $hasDetails, $groupId, $type) = $cat;

            if (
                (($groupId === $categoryId) && $category == $groupId) ||
                $category == $categoryId
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
     * @JMS\SerializedName("type")
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     *
     * @return string in/out
     */
    public function getType()
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
