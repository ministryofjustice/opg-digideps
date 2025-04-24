<?php

namespace App\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="money_short_category")
 */
class MoneyShortCategory
{
    /**
     * @param $type in|out
     *
     * @return array [ in =>  [typeId=>...], out=> [typeId=>...] ]
     */
    public static function getCategories($type)
    {
        return [
            'in' => [
                // typeId => options
                'state_pension_and_benefit' => [],
                'bequests' => [],
                'income_from_invesments_dividends_rental' => [],
                'sale_of_investments_property_assets' => [],
                'salary_or_wages' => [],
                'compensations_and_damages_awards' => [],
                'personal_pension' => [],
            ],
            'out' => [
                // typeId => options
                'accomodation_costs' => [],
                'care_fees' => [],
                'holidays' => [],
                'households_bills' => [],
                'personal_allowance' => [],
                'professional_fees' => [],
                'new_investments' => [],
                'travel_costs' => [],
            ]][$type];
    }

    /**
     * @var int
     *
     *
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="money_short_category_id_seq", allocationSize=1, initialValue=1)
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['moneyShortCategoriesIn', 'moneyShortCategoriesOut'])]
    private $id;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Report\Report", inversedBy="moneyShortCategories")
     *
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $report;

    /**
     * @var string
     *
     *
     * @ORM\Column(name="type_id", type="string", nullable=false)
     */
    #[JMS\Groups(['moneyShortCategoriesIn', 'moneyShortCategoriesOut'])]
    private $typeId;

    /**
     * @var bool
     *
     *
     *
     * @ORM\Column(name="present", type="boolean", nullable=true)
     */
    #[JMS\Type('boolean')]
    #[JMS\Groups(['moneyShortCategoriesIn', 'moneyShortCategoriesOut'])]
    private $present;

    /**
     * MoneyShortCategory constructor.
     *
     * @param string $typeId
     * @param bool   $present
     */
    public function __construct(Report $report, $typeId, $present)
    {
        $this->report = $report;
        $this->typeId = $typeId;
        $this->present = $present;
    }

    /**
     * Find the type (in/out) based on the.
     *
     * @return string in|out
     */
    public function getType()
    {
        foreach (['in', 'out'] as $type) {
            foreach (self::getCategories($type) as $typeId => $options) {
                if ($typeId == $this->typeId) {
                    return $type;
                }
            }
        }

        return null;
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
     * @return string
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * @param string $typeId
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * @return string
     */
    public function getPresent()
    {
        return $this->present;
    }

    /**
     * @param string $present
     */
    public function setPresent($present)
    {
        $this->present = $present;

        return $this;
    }
}
