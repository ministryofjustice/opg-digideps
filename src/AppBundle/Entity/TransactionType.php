<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="transaction_type")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"in" = "TransactionTypeIn", "out" = "TransactionTypeOut"})
 */
abstract class TransactionType
{

    public static $fixtures = [
        ['account-interest', false, '20', 'income-and-earnings', 'in'],
        ['dividends', false, '30', 'income-and-earnings', 'in'],
        ['income-from-property-rental', false, '50', 'income-and-earnings', 'in'],
        ['salary-or-wages', false, '60', 'income-and-earnings', 'in'],
        ['attendance-allowance', false, '70', 'state-benefits', 'in'],
        ['disability-living-allowance', false, '80', 'state-benefits', 'in'],
        ['employment-support-allowance', false, '90', 'state-benefits', 'in'],
        ['housing-benefit', false, '100', 'state-benefits', 'in'],
        ['incapacity-benefit', false, '110', 'state-benefits', 'in'],
        ['income-support', false, '120', 'state-benefits', 'in'],
        ['pension-credit', false, '130', 'state-benefits', 'in'],
        ['personal-independence-payment', false, '140', 'state-benefits', 'in'],
        ['severe-disablement-allowance', false, '150', 'state-benefits', 'in'],
        ['universal-credit', false, '160', 'state-benefits', 'in'],
        ['winter-fuel-cold-weather-payment', false, '170', 'state-benefits', 'in'],
        ['other-benefits', true, '180', 'state-benefits', 'in'],
        ['personal-pension', false, '190', 'pensions', 'in'],
        ['state-pension', false, '200', 'pensions', 'in'],
        ['compensation-or-damages-award', true, '210', 'damages', 'in'],
        ['bequest-or-inheritance', false, '220', 'one-off', 'in'],
        ['cash-gift-received', false, '230', 'one-off', 'in'],
        ['refunds', false, '240', 'one-off', 'in'],
        ['sale-of-asset', true, '250', 'one-off', 'in'],
        ['sale-of-investment', true, '260', 'one-off', 'in'],
        ['sale-of-property', true, '270', 'one-off', 'in'],
        ['anything-else', true, '290', 'moneyin-other', 'in'],
        ['broadband', false, '300', 'household-bills', 'out'],
        ['council-tax', false, '310', 'household-bills', 'out'],
        ['electricity', false, '320', 'household-bills', 'out'],
        ['food', false, '330', 'household-bills', 'out'],
        ['gas', false, '340', 'household-bills', 'out'],
        ['insurance-eg-life-home-contents', false, '350', 'household-bills', 'out'],
        ['other-insurance', false, '360', 'household-bills', 'out'],
        ['property-maintenance-improvement', true, '370', 'household-bills', 'out'],
        ['telephone', false, '380', 'household-bills', 'out'],
        ['tv-services', false, '390', 'household-bills', 'out'],
        ['water', false, '400', 'household-bills', 'out'],
        ['households-bills-other', true, '410', 'household-bills', 'out'],
        ['accommodation-service-charge', false, '420', 'accommodation', 'out'],
        ['mortgage', false, '430', 'accommodation', 'out'],
        ['rent', false, '440', 'accommodation', 'out'],
        ['accommodation-other', true, '450', 'accommodation', 'out'],
        ['care-fees', false, '460', 'care-and-medical', 'out'],
        ['local-authority-charges-for-care', false, '470', 'care-and-medical', 'out'],
        ['medical-expenses', false, '480', 'care-and-medical', 'out'],
        ['medical-insurance', false, '490', 'care-and-medical', 'out'],
        ['client-transport-bus-train-taxi-fares', false, '500', 'client-expenses', 'out'],
        ['clothes', false, '510', 'client-expenses', 'out'],
        ['day-trips', false, '520', 'client-expenses', 'out'],
        ['holidays', false, '530', 'client-expenses', 'out'],
        ['personal-allowance-pocket-money', false, '540', 'client-expenses', 'out'],
        ['toiletries', false, '550', 'client-expenses', 'out'],
        ['deputy-security-bond', false, '560', 'fees', 'out'],
        ['opg-fees', false, '570', 'fees', 'out'],
        ['other-fees', true, '580', 'fees', 'out'],
        ['professional-fees-eg-solicitor-accountant', true, '590', 'fees', 'out'],
        ['your-deputy-expenses', true, '600', 'fees', 'out'],
        ['investment-bonds-purchased', true, '610', 'major-purchases', 'out'],
        ['investment-account-purchased', true, '620', 'major-purchases', 'out'],
        ['purchase-over-1000', true, '630', 'major-purchases', 'out'],
        ['stocks-and-shares-purchased', true, '640', 'major-purchases', 'out'],
        ['gifts', true, '650', 'spending-on-other-people', 'out'],
        ['bank-charges', false, '660', 'debt-and-charges', 'out'],
        ['credit-cards-charges', false, '670', 'debt-and-charges', 'out'],
        ['unpaid-care-fees', false, '680', 'debt-and-charges', 'out'],
        ['loans', false, '690', 'debt-and-charges', 'out'],
        ['tax-payments-to-hmrc', false, '700', 'debt-and-charges', 'out'],
        ['debt-and-charges-other', true, '710', 'debt-and-charges', 'out'],
        ['cash-withdrawn', true, '720', 'moving-money', 'out'],
        ['transfers-out-to-other-accounts', true, '730', 'moving-money', 'out'],
        ['anything-else-paid-out', true, '740', 'moneyout-other', 'out'],
        ['other-incomes', true, '65', 'income-and-earnings', 'in'],
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="string", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * Discriminator (in/out).
     *
     * @var string
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="has_more_details", type="boolean", nullable=false)
     */
    private $hasMoreDetails;

    /**
     * @var int
     *
     * @ORM\Column(name="display_order", type="integer", nullable=true)
     */
    private $displayOrder;

    /**
     * @var TransactionTypeCategory
     *
     * @ORM\Column(name="category", type="string", nullable=false)
     *
     * @JMS\Type("string")
     */
    private $category;

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getHasMoreDetails()
    {
        return $this->hasMoreDetails;
    }

    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function setHasMoreDetails($hasMoreDetails)
    {
        $this->hasMoreDetails = $hasMoreDetails;

        return $this;
    }

    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }

    /**
     * @return TransactionTypeCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param TransactionTypeCategory $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
        
        return $this;
    }

}
