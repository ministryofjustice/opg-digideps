<?php

namespace App\Entity\Report;

use App\Entity\Report\Traits\HasBankAccountTrait;
use App\Entity\Traits\IsSoftDeleteableEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="money_transaction")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ORM\Entity
 */
class MoneyTransaction implements MoneyTransactionInterface
{
    use HasBankAccountTrait;
    use IsSoftDeleteableEntity;

    /**
     * Static list of possible money transaction categories.
     *
     * hasMoreDetails, order are not used any longer,
     *  but are left here for simplicity on future refactors / changes
     *
     * 'category' identifies the group and type
     * getGroup() and getType() use this array
     *
     * @JMS\Exclude
     */
    public static $categories = [
        // category | hasMoreDetails | order | group | type (in/out)

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
        ['professional-fees-eg-solicitor-accountant', true, 'fees', 'out'],
        ['professional-fees-eg-solicitor-accountant-non-lay', true, 'fees', 'out'],
        ['deputy-fees-and-expenses', true, 'fees', 'out'],

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
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="transaction_id_seq", allocationSize=1, initialValue=1)
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     */
    private $id;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Report\Report", inversedBy="moneyTransactions")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * Category (e.g. "dividends")
     * Once the category is known, group (income and dividends) and type (in) are known as well, see self::$categories.
     *
     * @var string
     *
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     * @ORM\Column(name="category", type="string", length=255, nullable=false)
     */
    private $category;

    /**
     * @var float
     *
     * @JMS\Type("string")
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=false)
     */
    private $amount;

    /**
     * @var string
     *
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * Used to serve migrations and recover data in case of errors
     * Remove when DDPB-1852 is fully released.
     *
     * @var string
     *
     * @ORM\Column(name="meta", type="text", nullable=true)
     */
    private $meta;

    /**
     * MoneyTransaction constructor.
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
        $report->addMoneyTransaction($this);
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param Report $report
     */
    public function setReport($report)
    {
        $this->report = $report;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;

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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the group based on the category.
     *
     * @JMS\VirtualProperty
     * @JMS\SerializedName("group")
     * @JMS\Groups({"transaction", "transactionsIn", "transactionsOut"})
     *
     * @return string in/out
     */
    public function getGroup()
    {
        foreach (self::$categories as $cat) {
            list($categoryId, $hasDetails, $groupId, $type) = $cat;
            if ($this->getCategory() == $categoryId) {
                return $groupId;
            }
        }

        return null;
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
